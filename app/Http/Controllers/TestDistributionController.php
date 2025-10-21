<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Results\CaasResultController;
use App\Http\Controllers\Results\DiscResultController;
use App\Http\Controllers\Results\TelitiResultController;
use App\Models\Candidate;
use App\Models\CandidateTest;
use App\Models\TestDistributionCandidate;
use App\Models\Test;
use App\Mail\TestInvitationMail;
use App\Models\CaasQuestion;
use App\Models\DiscQuestion;
use App\Models\TelitiQuestion;
use App\Models\CandidateAnswer;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\TestSection;
use App\Models\CaasOption;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TestDistributionController extends Controller
{
    /**
     * Display a listing of test distributions
     */
    public function index(Request $request)
    {
        $testCandidates = TestDistributionCandidate::with(['test'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->test_id, function ($query, $testId) {
                return $query->where('test_id', $testId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $distributions = $testCandidates->map(function ($testCandidate) {
            return [
                'id' => $testCandidate->id,
                'testName' => $testCandidate->test->name,
                'category' => $testCandidate->test->target_position ?? 'All Candidates',
                'startDate' => $testCandidate->created_at?->format('Y-m-d H:i:s'),
                'endDate' => $testCandidate->created_at?->addDays(7)->format('Y-m-d H:i:s'),
                'candidatesTotal' => 1, // Individual distribution
                'status' => $this->mapStatus($testCandidate->status),
                'candidate' => [
                    'id' => $testCandidate->id,
                    'name' => $testCandidate->name,
                    'email' => $testCandidate->email,
                ],
                'test' => [
                    'id' => $testCandidate->test->id,
                    'name' => $testCandidate->test->name,
                ],
                'created_at' => $testCandidate->created_at,
                'updated_at' => $testCandidate->updated_at,
            ];
        });

        return response()->json([
            'data' => $distributions,
            'meta' => [
                'total' => $distributions->count(),
            ],
        ]);
    }

    /**
     * Map internal status to frontend status
     */
    private function mapStatus($internalStatus)
    {
        switch ($internalStatus) {
            case TestDistributionCandidate::STATUS_PENDING:
                return 'Scheduled';
            case TestDistributionCandidate::STATUS_IN_PROGRESS:
                return 'Ongoing';
            case TestDistributionCandidate::STATUS_COMPLETED:
                return 'Completed';
            default:
                return 'Draft';
        }
    }

    /**
     * Create a new test invitation for candidates
     */
    public function inviteCandidates(Request $request)
    {
        \Log::info('Invite candidates request:', $request->all());
        
        $request->validate([
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'exists:test_distribution_candidates,id',
            'test_distribution_id' => 'required|exists:test_distributions,id',
            'custom_message' => 'nullable|string',
        ]);

        \Log::info('Validation passed, finding test distribution...');
        $testDistribution = \App\Models\TestDistribution::findOrFail($request->test_distribution_id);
        $test = $testDistribution->templateTest;
        \Log::info('Test distribution found:', ['id' => $testDistribution->id, 'name' => $testDistribution->name]);
        \Log::info('Template test found:', ['id' => $test->id, 'name' => $test->name]);

        // Cek duplikasi - hanya tolak jika status sudah 'invited' atau 'in_progress'
        // Status 'pending' masih bisa di-invite
        \Log::info('Checking for duplicates...');
        $duplicates = TestDistributionCandidate::whereIn('id', $request->candidate_ids)
            ->where('test_distribution_id', $testDistribution->id)
            ->whereIn('status', [TestDistributionCandidate::STATUS_INVITED, TestDistributionCandidate::STATUS_IN_PROGRESS])
            ->get();

        \Log::info('Duplicates found:', ['count' => $duplicates->count()]);

        if ($duplicates->isNotEmpty()) {
            \Log::info('Returning duplicate error');
            return response()->json([
                'message' => 'Some candidates already have active tests',
                'duplicates' => $duplicates->map(function ($tc) {
                    return [
                        'candidate_id' => $tc->id,
                        'candidate_name' => $tc->name,
                        'test_status' => $tc->status,
                        'invited_at' => $tc->created_at,
                    ];
                }),
            ], 422);
        }

        $invitations = [];

        \Log::info('Starting invitation process...');
        foreach ($request->candidate_ids as $testCandidateId) {
            \Log::info('Processing candidate ID:', ['id' => $testCandidateId]);
            $testCandidate = TestDistributionCandidate::findOrFail($testCandidateId);
            \Log::info('Candidate found:', ['name' => $testCandidate->name, 'status' => $testCandidate->status]);

            // Update status menjadi in_progress dan kirim email
            \Log::info('Updating candidate status to in_progress...');
            $testCandidate->update([
                'status' => TestDistributionCandidate::STATUS_IN_PROGRESS,
            ]);
            \Log::info('Status updated successfully');

            // Create Candidate model from TestDistributionCandidate dan simpan ke DB
            \Log::info('Creating Candidate model...');
            $candidate = Candidate::create([
                'name' => $testCandidate->name,
                'email' => $testCandidate->email,
                'position' => $testCandidate->position,
                'nik' => $testCandidate->nik,
                'phone_number' => $testCandidate->phone_number,
                'birth_date' => $testCandidate->birth_date,
                'gender' => $testCandidate->gender,
                'department' => $testCandidate->department,
            ]);
            \Log::info('Candidate model created & saved', ['id' => $candidate->id]);

            // Create CandidateTest model
            \Log::info('Creating CandidateTest model...');
            // Simpan CandidateTest ke database agar terhitung di daftar distribusi
            $candidateTest = CandidateTest::create([
                'candidate_id' => $candidate->id, // Gunakan ID dari Candidate yang baru dibuat
                'test_id' => $test->id,
                'test_distribution_id' => $testDistribution->id,
                'unique_token' => (string) Str::uuid(),
                'status' => CandidateTest::STATUS_NOT_STARTED,
            ]);
            \Log::info('CandidateTest model created & saved', ['id' => $candidateTest->id]);

            // Kirim email invitation
            \Log::info('Sending email invitation...');
            try {
                // Use send instead of queue for immediate error feedback
                Mail::to($testCandidate->email)->send(new TestInvitationMail(
                    $candidate,
                    $candidateTest,
                    $test,
                    $request->custom_message
                ));
                \Log::info('Email sent successfully');
            } catch (\Exception $e) {
                \Log::error('Email sending failed: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                throw $e;
            }

            $invitations[] = $testCandidate;
        }

        // Log activity: HRD inviting candidates to test
        $candidateCount = count($request->candidate_ids);
        $testName = $test->name;
        LogActivityService::addToLog("Invited {$candidateCount} candidates to test: {$testName}", $request);

        return response()->json([
            'message' => 'Test invitations sent successfully',
            'data' => $invitations,
            'duplicate' => $duplicates,
        ]);
    }

    /**
     * Resend invitation
     */
    public function resendInvitation(Request $request, $candidateTestId)
    {
        $candidateTest = CandidateTest::findOrFail($candidateTestId);
        $test = $candidateTest->test;
        $candidate = $candidateTest->candidate;

        // Check if user has permission to resend invitation
        if (!auth()->user() || auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $newToken = $candidateTest->regenerateToken();

        Mail::to($candidate->email)->send(new TestInvitationMail(
            $candidate,
            $candidateTest,
            $test,
            $request->custom_message
        ));

        // Log activity: HRD resending test invitation
        LogActivityService::addToLog("Resent test invitation to candidate: {$candidate->name} for test: {$test->name}", $request);

        return response()->json([
            'message' => 'Invitation resent successfully',
            'new_token' => $newToken,
        ]);
    }

    /**
     * Start the test (via token)
     */
    public function startTest(Request $request, $token)
    {
        $candidateTest = CandidateTest::where('unique_token', $token)
            ->with(['test.sections', 'candidate'])
            ->firstOrFail();

        if ($candidateTest->status === CandidateTest::STATUS_COMPLETED) {
            return response()->json([
                'status' => 'completed',
                'completed_at' => $candidateTest->completed_at,
                'message' => 'This test has already been completed.'
            ], 403);
        }

        if ($candidateTest->isExpired()) {
            abort(403, 'This test link has expired.');
        }

        if ($candidateTest->status === CandidateTest::STATUS_NOT_STARTED) {
            $candidateTest->markAsStarted();
            // Log activity: Candidate started test
            LogActivityService::addToLog("Candidate started test: {$candidateTest->test->name} (Candidate: {$candidateTest->candidate->name})", $request);
        }

        // Get sections with their questions
        $sections = $candidateTest->test->sections()->with(['testQuestions' => function($query) {
            $query->inRandomOrder();
        }])->orderBy('sequence')->get();

        // If no sections, fallback to direct questions
        $questions = null;
        if ($sections->isEmpty()) {
            $questions = $candidateTest->test
                ->testQuestions()
                ->inRandomOrder()
                ->get();
        }

        return response()->json([
            'test' => $candidateTest->test,
            'candidate' => $candidateTest->candidate,
            'started_at' => $candidateTest->started_at,
            'sections' => $sections->isEmpty() ? null : $sections,
            'questions' => $questions,
        ]);
    }

    /**
     * Submit test (simpan jawaban saja)
     */
    public function submitTest(Request $request, $token)
    {
        DB::beginTransaction();
        try {
            $candidateTest = CandidateTest::where('unique_token', $token)
                ->with(['test'])
                ->firstOrFail();

            if ($candidateTest->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu tes sudah habis'
                ], 400);
            }

            if ($candidateTest->status === CandidateTest::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tes sudah disubmit sebelumnya'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*.section_id' => 'required|exists:test_sections,id',
                'answers.*.question_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->answers as $index => $answer) {
                $section = TestSection::find($answer['section_id']);
                $rules = [];

                switch (strtolower($section->section_type)) {
                    case 'disc':
                        $rules = [
                            'most_option_id' => 'required|exists:disc_options,id',
                            'least_option_id' => 'required|exists:disc_options,id',
                        ];
                        break;

                    case 'teliti':
                        $rules = [
                            'selected_option_id' => 'required|exists:teliti_options,id',
                        ];
                        break;

                    case 'caas':
                        $rules = [
                            'selected_option_id' => 'required|exists:caas_options,id',
                        ];
                        break;
                }

                $sectionValidator = Validator::make($answer, $rules);
                if ($sectionValidator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Validasi gagal pada jawaban index $index",
                        'errors' => $sectionValidator->errors()
                    ], 422);
                }
            }

            foreach ($request->answers as $answerData) {
                $this->saveAnswer($candidateTest->id, $answerData);
            }

            $candidateTest->update([
                'status' => CandidateTest::STATUS_COMPLETED,
                'completed_at' => now(),
                'time_spent' => $this->calculateTimeSpent($candidateTest)
            ]);

            DB::commit();

            // Trigger untuk Kalkulasi
            if ($candidateTest->test->sections) {
                foreach ($candidateTest->test->sections as $section) {
                    switch (strtolower($section->section_type)) {
                        case 'caas':
                            app(CaasResultController::class)
                                ->calculateByIds($candidateTest->id, $section->id);
                            break;

                        case 'disc':
                            app(DiscResultController::class)
                                ->calculateByIds($candidateTest->id, $section->id);
                            break;

                        case 'teliti':
                            app(TelitiResultController::class)
                                ->calculateByIds($candidateTest->id, $section->id);
                            break;
                    }
                }
            }

            // Log activity: Candidate submitted test
            LogActivityService::addToLog("Candidate submitted test: {$candidateTest->test->name} (Candidate: {$candidateTest->candidate->name})", $request);

            return response()->json([
                'success' => true,
                'message' => 'Tes berhasil disubmit',
                'completion_time' => $candidateTest->completed_at->format('Y-m-d H:i:s'),
                'confirmation_code' => $this->generateConfirmationCode(),
                'candidate_test_id' => $candidateTest->id
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah expired'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan jawaban individual tanpa perhitungan skor
     */
    private function saveAnswer($candidateTestId, $answerData)
    {
        $section = TestSection::findOrFail($answerData['section_id']);
        $sectionType = strtolower($section->section_type);

        $data = [
            'candidate_test_id' => $candidateTestId,
            'section_id' => $answerData['section_id'],
            'question_id' => $answerData['question_id'],
        ];

        switch ($sectionType) {
            case 'disc':
                $data['most_option_id']  = $answerData['most_option_id'];
                $data['least_option_id'] = $answerData['least_option_id'];
                break;

            case 'teliti':
                $data['selected_option_id'] = $answerData['selected_option_id'];
                $question = TelitiQuestion::findOrFail($answerData['question_id']);
                $data['is_correct'] = $question->correct_option_id == $answerData['selected_option_id'];
                break;

            case 'caas':
                $data['selected_option_id'] = $answerData['selected_option_id'];
                $option = CaasOption::findOrFail($answerData['selected_option_id']);
                $data['score'] = $option->score;
                break;
        }

        CandidateAnswer::updateOrCreate(
            [
                'candidate_test_id' => $candidateTestId,
                'section_id' => $answerData['section_id'],
                'question_id' => $answerData['question_id'],
            ],
            $data
        );
    }

    /**
     * Validasi sebelum submit (cek jumlah soal terjawab)
     */
    public function validateBeforeSubmit(Request $request, $token)
    {
        try {
            $candidateTest = CandidateTest::where('unique_token', $token)
                ->with(['test.testQuestions'])
                ->firstOrFail();

            if ($candidateTest->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu tes sudah habis'
                ], 400);
            }

            if ($candidateTest->status === CandidateTest::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tes sudah disubmit sebelumnya'
                ], 400);
            }

            $totalQuestions = $candidateTest->test->testQuestions->count();
            $answeredQuestions = CandidateAnswer::where('candidate_test_id', $candidateTest->id)->count();

            $answeredQuestionIds = CandidateAnswer::where('candidate_test_id', $candidateTest->id)
                ->pluck('question_id')
                ->toArray();

            $unansweredQuestions = $candidateTest->test->testQuestions()
                ->whereNotIn('id', $answeredQuestionIds)
                ->pluck('id')
                ->toArray();

            $timeRemaining = $this->calculateTimeRemaining($candidateTest);

            return response()->json([
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'unanswered_questions' => count($unansweredQuestions),
                'unanswered_question_ids' => $unansweredQuestions,
                'time_remaining' => $timeRemaining,
                'can_submit' => $answeredQuestions >= $totalQuestions || $timeRemaining <= 0,
                'test_status' => $candidateTest->status
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah expired'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateTimeSpent($candidateTest)
    {
        if ($candidateTest->started_at && $candidateTest->completed_at) {
            return $candidateTest->started_at->diffInSeconds($candidateTest->completed_at);
        }
        return null;
    }

    private function calculateTimeRemaining($candidateTest)
    {
        if (!$candidateTest->started_at) {
            return $candidateTest->test->time_limit * 60;
        }
        $elapsedTime = now()->diffInSeconds($candidateTest->started_at);
        $totalTimeLimit = $candidateTest->test->time_limit * 60;
        return max(0, $totalTimeLimit - $elapsedTime);
    }

    private function generateConfirmationCode()
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    /**
     * Remove the specified test distribution
     */
    public function destroy($id)
    {
        try {
            $testCandidate = TestDistributionCandidate::findOrFail($id);
            
            // Delete related answers first
            $testCandidate->candidateAnswers()->delete();
            
            // Delete the test candidate
            $testCandidate->delete();

            // Log activity: HRD deleting test distribution
            LogActivityService::addToLog("Deleted test distribution: {$testCandidate->test->name} for candidate: {$testCandidate->name}", request());

            return response()->json([
                'status' => 'success',
                'message' => 'Test distribution deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test distribution not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete test distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autoSubmitExpiredTests()
    {
        $expiredTests = CandidateTest::where('status', CandidateTest::STATUS_IN_PROGRESS)
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        foreach ($expiredTests as $test) {
            DB::transaction(function () use ($test) {
                $test->update([
                    'status' => CandidateTest::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'time_spent' => $this->calculateTimeSpent($test),
                    'is_auto_submitted' => true
                ]);
            });
        }

        return count($expiredTests) . ' tes telah disubmit otomatis';
    }

    /**
     * Delete test distribution (only delete candidate tests, keep test package)
     */
    public function deleteDistribution(Request $request, $distributionId)
    {
        try {
            $distribution = \App\Models\TestDistribution::findOrFail($distributionId);
            
            // Skip authentication check for public endpoint
            // if (!auth()->user() || (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin')) {
            //     abort(403, 'Unauthorized action.');
            // }

            // Check if there are any completed tests - prevent deletion if tests are completed
            $completedTests = $distribution->candidateTests()->where('status', CandidateTest::STATUS_COMPLETED)->count();
            if ($completedTests > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete test distribution that has completed tests'
                ], 422);
            }

            // Delete related candidate tests and test distribution candidates
            $candidateTestsCount = $distribution->candidateTests()->count();
            $distribution->candidateTests()->delete();
            $distribution->candidates()->delete();
            
            // Delete the test distribution record
            $distribution->delete();

            // Log activity
            LogActivityService::addToLog("Deleted test distribution: {$distribution->name} (removed {$candidateTestsCount} candidate tests)", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test distribution deleted successfully. Template test package preserved for future use.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test distribution not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete test distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new distribution from existing test package (reuse same test package)
     */
    public function createDistributionFromPackage(Request $request)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'session_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $testPackage = Test::findOrFail($request->test_id);
            
            // Create test distribution record (not duplicate test package)
            $currentDate = now()->format('d/m/Y');
            $distributionName = $testPackage->name . ' - ' . $currentDate;
            
            // Create test distribution record
            $testDistribution = \App\Models\TestDistribution::create([
                'name' => $distributionName,
                'template_test_id' => $testPackage->id,
                'target_position' => $testPackage->target_position,
                'icon_path' => $testPackage->icon_path,
                'started_date' => $request->start_date,
                'ended_date' => $request->end_date,
                'access_type' => $testPackage->access_type,
                'status' => 'Scheduled',
            ]);

            // Log activity
            LogActivityService::addToLog("Created test distribution '{$distributionName}' from template '{$testPackage->name}'", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test distribution created successfully',
                'data' => $testDistribution
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active test distributions
     */
    public function getActiveDistributions(Request $request)
    {
        try {
            // Get all test distributions from the new table
            $distributions = \App\Models\TestDistribution::with(['candidateTests.candidate', 'candidates'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($distribution) {
                    $candidateTests = $distribution->candidateTests;
                    $distributionCandidates = $distribution->candidates;
                    $completedCount = $candidateTests->where('status', CandidateTest::STATUS_COMPLETED)->count();
                    $inProgressCount = $candidateTests->where('status', CandidateTest::STATUS_IN_PROGRESS)->count();
                    $notStartedCount = $candidateTests->where('status', CandidateTest::STATUS_NOT_STARTED)->count();
                    
                    // Determine overall status
                    $status = $distribution->status;
                    if ($completedCount > 0 && $inProgressCount == 0 && $notStartedCount == 0) {
                        $status = 'Completed';
                    } elseif ($inProgressCount > 0 || $notStartedCount > 0) {
                        $status = 'In Progress';
                    }

                    return [
                        'id' => $distribution->id,
                        'name' => $distribution->name,
                        'target_position' => $distribution->target_position,
                        // Format tanggal agar tidak jadi ISO/UTC di frontend
                        'started_date' => $distribution->started_date ? $distribution->started_date->format('Y-m-d') : null,
                        'candidates_count' => $distributionCandidates->count(),
                        'completed_count' => $completedCount,
                        'in_progress_count' => $inProgressCount,
                        'not_started_count' => $notStartedCount,
                        'status' => $status,
                        'created_at' => $distribution->created_at,
                        'updated_at' => $distribution->updated_at,
                    ];
                });

            // Log activity
            LogActivityService::addToLog("Viewed test distributions", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test distributions retrieved successfully',
                'data' => $distributions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve test distributions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all candidate tests for results page
     */
    public function getAllCandidateTests(Request $request)
    {
        try {
            $candidateTests = \App\Models\CandidateTest::with(['candidate', 'test'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Candidate tests retrieved successfully',
                'data' => $candidateTests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve candidate tests: ' . $e->getMessage()
            ], 500);
        }
    }
}
