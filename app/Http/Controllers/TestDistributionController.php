<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Results\CaasResultController;
use App\Http\Controllers\Results\DiscResultController;
use App\Http\Controllers\Results\TelitiResultController;
use App\Models\Candidate;
use App\Models\CandidateTest;
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
     * Create a new test invitation for candidates
     */
    public function inviteCandidates(Request $request)
    {
        $request->validate([
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'exists:candidates,id',
            'test_id' => 'required|exists:tests,id',
            'custom_message' => 'nullable|string',
        ]);

        $test = Test::findOrFail($request->test_id);

        $duplicates = CandidateTest::whereIn('candidate_id', $request->candidate_ids)
            ->where('test_id', $test->id)
            ->where('status', '!=', CandidateTest::STATUS_COMPLETED)
            ->with('candidate')
            ->get();

        if ($duplicates->isNotEmpty()) {
            return response()->json([
                'message' => 'Some candidates already have active tests',
                'duplicates' => $duplicates->map(function ($ct) {
                    return [
                        'candidate_id' => $ct->candidate_id,
                        'candidate_name' => $ct->candidate->name,
                        'test_status' => $ct->status,
                        'invited_at' => $ct->created_at,
                    ];
                }),
            ], 422);
        }

        $invitations = [];

        foreach ($request->candidate_ids as $candidateId) {
            $candidate = Candidate::findOrFail($candidateId);

            $candidateTest = CandidateTest::create([
                'candidate_id' => $candidate->id,
                'test_id' => $test->id,
                'unique_token' => Str::uuid(),
                'status' => CandidateTest::STATUS_NOT_STARTED,
            ]);

            Mail::to($candidate->email)->queue(new TestInvitationMail(
                $candidate,
                $candidateTest,
                $test,
                $request->custom_message
            ));

            $invitations[] = $candidateTest;
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
            ->with(['test', 'candidate'])
            ->firstOrFail();

        if ($candidateTest->status === CandidateTest::STATUS_COMPLETED) {
            abort(403, 'This test has already been completed.');
        }

        if ($candidateTest->isExpired()) {
            abort(403, 'This test link has expired.');
        }

        if ($candidateTest->status === CandidateTest::STATUS_NOT_STARTED) {
            $candidateTest->markAsStarted();
            // Log activity: Candidate started test
            LogActivityService::addToLog("Candidate started test: {$candidateTest->test->name} (Candidate: {$candidateTest->candidate->name})", $request);
        }

        $questions = $candidateTest->test
            ->testQuestions()
            ->inRandomOrder()
            ->get();

        return response()->json([
            'test' => $candidateTest->test,
            'candidate' => $candidateTest->candidate,
            'started_at' => $candidateTest->started_at,
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

    public function autoSubmitExpiredTests()
    {
        $expiredTests = CandidateTest::where('status', CandidateTest::STATUS_IN_PROGRESS)
            ->where('test_expires_at', '<', now())
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
}
