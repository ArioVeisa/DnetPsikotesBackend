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
        
        // Set max execution time untuk request ini (120 detik)
        // Ini penting karena email sending bisa lambat jika SMTP tidak dikonfigurasi dengan benar
        set_time_limit(120);
        
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
        $failedEmails = [];
        $mailDriver = config('mail.default');

        \Log::info('Starting invitation process...');
        \Log::info('Mail driver: ' . $mailDriver);
        
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
            try {
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
            } catch (\Exception $e) {
                // Jika ada duplicate NIK/email, cari candidate yang sudah ada
                \Log::info('Candidate already exists, finding existing candidate...');
                $candidate = Candidate::where('nik', $testCandidate->nik)
                    ->orWhere('email', $testCandidate->email)
                    ->first();
                
                if (!$candidate) {
                    \Log::error('Failed to create or find candidate: ' . $e->getMessage());
                    $failedEmails[] = [
                        'email' => $testCandidate->email,
                        'name' => $testCandidate->name,
                        'reason' => 'Failed to create or find candidate',
                    ];
                    continue; // Skip candidate ini dan lanjut ke yang berikutnya
                }
                \Log::info('Using existing candidate', ['id' => $candidate->id]);
            }

            // Create CandidateTest model
            \Log::info('Creating CandidateTest model...');
            try {
                // Simpan CandidateTest ke database agar terhitung di daftar distribusi
                $candidateTest = CandidateTest::create([
                    'candidate_id' => $candidate->id, // Gunakan ID dari Candidate yang baru dibuat
                    'test_id' => $test->id,
                    'test_distribution_id' => $testDistribution->id,
                    'unique_token' => (string) Str::uuid(),
                    'status' => CandidateTest::STATUS_NOT_STARTED,
                ]);
                \Log::info('CandidateTest model created & saved', ['id' => $candidateTest->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to create CandidateTest: ' . $e->getMessage());
                $failedEmails[] = [
                    'email' => $testCandidate->email,
                    'name' => $testCandidate->name,
                    'reason' => 'Failed to create CandidateTest',
                ];
                continue; // Skip candidate ini dan lanjut ke yang berikutnya
            }

            // Kirim email invitation
            // Note: Email sending bisa lambat jika SMTP tidak dikonfigurasi dengan benar
            // Untuk development, bisa gunakan MAIL_MAILER=log untuk testing
            \Log::info('Sending email invitation to: ' . $testCandidate->email);
            $emailSent = false;
            $emailError = null;
            
            try {
                $startTime = microtime(true);
                
                Mail::to($testCandidate->email)->send(new TestInvitationMail(
                    $candidate,
                    $candidateTest,
                    $test,
                    $request->custom_message
                ));
                
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                $emailSent = true;
                \Log::info('Email sent successfully to: ' . $testCandidate->email . ' (took ' . $duration . 's)');
                
                // Only add to invitations if email was actually sent
                $invitations[] = $testCandidate;
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                // Handle SMTP/transport connection errors (Symfony Mailer)
                $emailError = $e->getMessage();
                \Log::error('Email transport error for ' . $testCandidate->email . ': ' . $emailError);
                \Log::error('Full exception: ' . $e->getTraceAsString());
                
                $failedEmails[] = [
                    'email' => $testCandidate->email,
                    'name' => $testCandidate->name,
                    'reason' => 'Email transport error: ' . $emailError,
                ];
            } catch (\Exception $e) {
                // Handle other email errors
                $emailError = $e->getMessage();
                \Log::error('Email sending failed for ' . $testCandidate->email . ': ' . $emailError);
                \Log::error('Exception class: ' . get_class($e));
                \Log::error('Full exception: ' . $e->getTraceAsString());
                
                $failedEmails[] = [
                    'email' => $testCandidate->email,
                    'name' => $testCandidate->name,
                    'reason' => 'Email sending failed: ' . $emailError,
                ];
            }
            
            // Log warning if using 'log' driver (emails are not actually sent)
            if ($mailDriver === 'log' && $emailSent) {
                \Log::warning('⚠️ MAIL_MAILER is set to "log" - email was logged but NOT actually sent to ' . $testCandidate->email);
                \Log::warning('⚠️ Check storage/logs/laravel.log for the email content');
            }
        }

        // Log activity: HRD inviting candidates to test
        $candidateCount = count($request->candidate_ids);
        $testName = $test->name;
        LogActivityService::addToLog("Invited {$candidateCount} candidates to test: {$testName}", $request);

        $successCount = count($invitations);
        $totalRequested = count($request->candidate_ids);
        $failedCount = count($failedEmails);
        
        // Build response message
        $message = "";
        $warningMessage = "";
        
        if ($mailDriver === 'log') {
            $warningMessage = "⚠️ WARNING: MAIL_MAILER is set to 'log'. Emails are logged but NOT actually sent. Check storage/logs/laravel.log for email content.";
            \Log::warning($warningMessage);
        }
        
        if ($successCount === $totalRequested && $failedCount === 0) {
            $message = "Berhasil mengirim undangan test ke {$successCount} kandidat";
        } elseif ($successCount > 0) {
            $message = "Berhasil mengirim undangan test ke {$successCount} dari {$totalRequested} kandidat";
            if ($failedCount > 0) {
                $message .= ". {$failedCount} email gagal dikirim.";
            }
        } else {
            $message = "Tidak ada undangan yang berhasil dikirim";
            if ($failedCount > 0) {
                $message .= ". {$failedCount} email gagal dikirim.";
            } else {
                $message .= ". Semua kandidat mungkin sudah terdaftar sebelumnya.";
            }
        }
        
        // Determine success status
        $success = $successCount > 0;
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $invitations,
            'success_count' => $successCount,
            'total_requested' => $totalRequested,
            'failed_count' => $failedCount,
        ];
        
        // Add warning if using log driver
        if ($warningMessage) {
            $response['warning'] = $warningMessage;
        }
        
        // Add failed emails details if any
        if ($failedCount > 0) {
            $response['failed_emails'] = $failedEmails;
        }
        
        // Log summary
        \Log::info('Invitation process completed:', [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'total_requested' => $totalRequested,
            'mail_driver' => $mailDriver,
        ]);
        
        // Return appropriate HTTP status
        $statusCode = $success ? 200 : 207; // 207 Multi-Status if partial success
        
        return response()->json($response, $statusCode);
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
     * Resend invitations for multiple candidates (using test_distribution_candidate_id)
     */
    public function resendInvitations(Request $request)
    {
        $request->validate([
            'test_distribution_id' => 'required|exists:test_distributions,id',
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'exists:test_distribution_candidates,id',
        ]);

        $testDistribution = \App\Models\TestDistribution::findOrFail($request->test_distribution_id);
        $test = $testDistribution->templateTest;
        $sent = 0;
        $failed = 0;

        foreach ($request->candidate_ids as $testDistributionCandidateId) {
            try {
                $testDistributionCandidate = TestDistributionCandidate::findOrFail($testDistributionCandidateId);
                
                // Cari atau buat CandidateTest
                $candidateTest = CandidateTest::where('test_distribution_id', $testDistribution->id)
                    ->whereHas('candidate', function($q) use ($testDistributionCandidate) {
                        $q->where('email', $testDistributionCandidate->email);
                    })
                    ->first();

                if (!$candidateTest) {
                    // Jika belum ada CandidateTest, buat baru
                    $candidate = Candidate::where('email', $testDistributionCandidate->email)->first();
                    if (!$candidate) {
                        $candidate = Candidate::create([
                            'nik' => $testDistributionCandidate->nik ?? '',
                            'name' => $testDistributionCandidate->name,
                            'phone_number' => $testDistributionCandidate->phone_number ?? '',
                            'email' => $testDistributionCandidate->email,
                            'position' => $testDistributionCandidate->position ?? '',
                            'birth_date' => $testDistributionCandidate->birth_date ?? now()->format('Y-m-d'),
                            'gender' => $testDistributionCandidate->gender ?? 'male',
                            'department' => $testDistributionCandidate->department ?? '',
                        ]);
                    }

                    $candidateTest = CandidateTest::create([
                        'candidate_id' => $candidate->id,
                        'test_id' => $test->id,
                        'test_distribution_id' => $testDistribution->id,
                        'unique_token' => (string) Str::uuid(),
                        'status' => CandidateTest::STATUS_NOT_STARTED,
                    ]);
                }

                // Regenerate token
                $newToken = $candidateTest->regenerateToken();

                // Kirim email
                Mail::to($testDistributionCandidate->email)->send(new TestInvitationMail(
                    $candidateTest->candidate,
                    $candidateTest,
                    $test,
                    $request->custom_message
                ));

                $sent++;
            } catch (\Exception $e) {
                \Log::error('Failed to resend invitation for test_distribution_candidate_id: ' . $testDistributionCandidateId, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully resent {$sent} invitation(s). " . ($failed > 0 ? "{$failed} failed." : ''),
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

    /**
     * Start the test (via token)
     */
    public function startTest(Request $request, $token)
    {
        $candidateTest = CandidateTest::where('unique_token', $token)
            ->with(['test.sections', 'candidate', 'testDistribution'])
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

        // Validate session time (start_date and end_date from test distribution)
        $testDistribution = $candidateTest->testDistribution;
        if ($testDistribution) {
            $now = now();
            $startDate = $testDistribution->started_date;
            $endDate = $testDistribution->ended_date;

            // Check if test session has started
            if ($startDate && $now < $startDate) {
                return response()->json([
                    'error' => 'TEST_NOT_STARTED',
                    'message' => 'The test session has not started yet.',
                    'start_date' => $startDate->toISOString(),
                    'end_date' => $endDate ? $endDate->toISOString() : null,
                ], 403);
            }

            // Check if test session has ended
            if ($endDate && $now > $endDate) {
                return response()->json([
                    'error' => 'TEST_SESSION_ENDED',
                    'message' => 'The test session has ended.',
                    'start_date' => $startDate ? $startDate->toISOString() : null,
                    'end_date' => $endDate->toISOString(),
                ], 403);
            }
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

        \Log::info("startTest: Found {$sections->count()} sections for test {$candidateTest->test->id}");

        // Transform sections to include question details
        $sections->transform(function ($section) {
            \Log::info("startTest: Section {$section->id} has {$section->testQuestions->count()} questions");
            
            $section->test_questions = $section->testQuestions->map(function ($testQuestion) {
                \Log::info("startTest: Processing question {$testQuestion->id}, type: {$testQuestion->question_type}");
                \Log::info("startTest: Question detail: " . json_encode($testQuestion->question_detail));
                
                return [
                    'id' => $testQuestion->id,
                    'question_id' => $testQuestion->question_id,
                    'question_type' => $testQuestion->question_type,
                    'question_detail' => $testQuestion->question_detail
                ];
            });
            // Also keep 'questions' for backward compatibility
            $section->questions = $section->test_questions;
            unset($section->testQuestions); // Remove the original relation
            return $section;
        });

        // If no sections, fallback to direct questions
        $questions = null;
        if ($sections->isEmpty()) {
            $questions = $candidateTest->test
                ->testQuestions()
                ->inRandomOrder()
                ->get()
                ->map(function ($testQuestion) {
                    return [
                        'id' => $testQuestion->id,
                        'question_id' => $testQuestion->question_id,
                        'question_type' => $testQuestion->question_type,
                        'question_detail' => $testQuestion->question_detail
                    ];
                });
        }

        // Get start_date and end_date from test distribution
        $startDate = null;
        $endDate = null;
        if ($testDistribution) {
            $startDate = $testDistribution->started_date ? $testDistribution->started_date->toISOString() : null;
            $endDate = $testDistribution->ended_date ? $testDistribution->ended_date->toISOString() : null;
        }

        return response()->json([
            'test' => $candidateTest->test,
            'candidate' => $candidateTest->candidate,
            'started_at' => $candidateTest->started_at,
            'start_date' => $startDate, // Session start date/time
            'end_date' => $endDate,     // Session end date/time
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
                // Test already completed - return success instead of error
                // This can happen if user clicks submit multiple times or refreshes page
                return response()->json([
                    'success' => true,
                    'message' => 'Tes sudah disubmit sebelumnya',
                    'completion_time' => $candidateTest->completed_at ? $candidateTest->completed_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'confirmation_code' => $this->generateConfirmationCode(),
                    'candidate_test_id' => $candidateTest->id
                ], 200);
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
                
                if (!$section) {
                    return response()->json([
                        'success' => false,
                        'message' => "Section tidak ditemukan pada jawaban index $index",
                        'errors' => ['section_id' => ['Section tidak valid']]
                    ], 422);
                }
                
                $rules = [];

                switch (strtolower($section->section_type)) {
                    case 'disc':
                        $rules = [
                            'most_option_id' => 'required|exists:disc_options,id',
                            'least_option_id' => 'required|exists:disc_options,id',
                        ];
                        break;

                    case 'teliti':
                    case 'fast accuracy':
                        $rules = [
                            'selected_option_id' => 'required|exists:teliti_options,id',
                        ];
                        break;

                    case 'caas':
                        $rules = [
                            'selected_option_id' => 'required|exists:caas_options,id',
                        ];
                        break;
                        
                    default:
                        return response()->json([
                            'success' => false,
                            'message' => "Tipe section tidak valid: {$section->section_type}",
                            'errors' => ['section_type' => ['Tipe section tidak dikenali']]
                        ], 422);
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

            // Send email notification to admin
            try {
                $emailService = app(\App\Services\TestCompletionEmailService::class);
                $emailService->sendCompletionNotification($candidateTest);
            } catch (\Exception $e) {
                // Log error but don't fail the test completion
                \Log::error('Failed to send completion email notification', [
                    'candidate_test_id' => $candidateTest->id,
                    'error' => $e->getMessage(),
                ]);
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

        // Frontend sekarang mengirim test_question.id (bukan question_id asli)
        $testQuestion = \App\Models\TestQuestion::find($answerData['question_id']);
        
        $data = [
            'candidate_test_id' => $candidateTestId,
            'section_id' => $answerData['section_id'],
            'question_id' => $answerData['question_id'], // Simpan test_question.id
        ];

        switch ($sectionType) {
            case 'disc':
                $data['most_option_id']  = $answerData['most_option_id'];
                $data['least_option_id'] = $answerData['least_option_id'];
                break;

            case 'teliti':
                $data['selected_option_id'] = $answerData['selected_option_id'];
                // $testQuestion sudah dicari di atas, gunakan untuk validasi
                if ($testQuestion && $testQuestion->question_type === 'teliti') {
                    $question = TelitiQuestion::find($testQuestion->question_id);
                    if ($question) {
                        $data['is_correct'] = $question->correct_option_id == $answerData['selected_option_id'];
                    }
                }
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
    public function deleteDistribution(Request $request, $testId)
    {
        try {
            $distribution = \App\Models\TestDistribution::findOrFail($testId);
            
            // Skip authentication check for public endpoint
            // if (!auth()->user() || (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'admin')) {
            //     abort(403, 'Unauthorized action.');
            // }

            // Allow deletion even if tests are completed
            // Just log the information for audit purposes
            $completedTests = $distribution->candidateTests()->where('status', CandidateTest::STATUS_COMPLETED)->count();
            $inProgressTests = $distribution->candidateTests()->where('status', CandidateTest::STATUS_IN_PROGRESS)->count();
            
            if ($completedTests > 0 || $inProgressTests > 0) {
                // Log warning but allow deletion
                \Log::warning("Deleting test distribution with completed/in-progress tests", [
                    'distribution_id' => $distribution->id,
                    'distribution_name' => $distribution->name,
                    'completed_tests' => $completedTests,
                    'in_progress_tests' => $inProgressTests
                ]);
            }

            // Delete related candidate tests and test distribution candidates
            $candidateTestsCount = $distribution->candidateTests()->count();
            $distribution->candidateTests()->delete();
            $distribution->candidates()->delete();
            
            // Delete the test distribution record
            $distribution->delete();

            // Log activity
            LogActivityService::addToLog("Deleted test distribution: {$distribution->name} (removed {$candidateTestsCount} candidate tests)", $request);

            $message = 'Test distribution deleted successfully. Template test package preserved for future use.';
            if ($completedTests > 0 || $inProgressTests > 0) {
                $message .= " Note: {$completedTests} completed tests and {$inProgressTests} in-progress tests were also removed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message
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
            'start_date' => 'required|date', // Accepts both date and datetime
            'end_date' => 'required|date|after:start_date', // Accepts both date and datetime
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


    /**
     * Delete individual candidate test result
     */
    public function deleteResult($candidateTestId)
    {
        try {
            DB::beginTransaction();

            // Log activity before deletion (skip if candidate doesn't exist)
            try {
                LogActivityService::addToLog(
                    'DELETE_INDIVIDUAL_RESULT',
                    request(),
                    'success',
                    [
                        'entity_type' => 'candidate_test',
                        'entity_id' => $candidateTestId
                    ]
                );
            } catch (\Exception $logError) {
                // Continue without logging if there's an error
                \Log::warning('Failed to log delete activity', ['error' => $logError->getMessage()]);
            }

            // Find candidate test by ID
            $candidateTest = CandidateTest::find($candidateTestId);
            
            if (!$candidateTest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate test not found'
                ], 404);
            }

            // Delete candidate answers for this test
            $answersDeleted = CandidateAnswer::where('candidate_test_id', $candidateTest->id)->delete();
            
            // Delete result records for this test
            $discResultsDeleted = \App\Models\DiscResult::where('candidate_test_id', $candidateTest->id)->delete();
            $caasResultsDeleted = \App\Models\CaasResult::where('candidate_test_id', $candidateTest->id)->delete();
            $telitiResultsDeleted = \App\Models\TelitiResult::where('candidate_test_id', $candidateTest->id)->delete();
            
            // Reset candidate test to not started
            $candidateTest->update([
                'status' => 'not_started',
                'completed_at' => null,
                'score' => null,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Individual result deleted successfully',
                'data' => [
                    'candidate_test_id' => $candidateTest->id,
                    'candidate_id' => $candidateTest->candidate_id,
                    'answers_deleted' => $answersDeleted,
                    'disc_results_deleted' => $discResultsDeleted,
                    'caas_results_deleted' => $caasResultsDeleted,
                    'teliti_results_deleted' => $telitiResultsDeleted,
                    'candidate_test_reset' => 'Candidate test reset to not started'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to delete individual result', [
                'candidate_test_id' => $candidateTestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete individual result',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test results for a specific candidate test
     */
    public function getResults(Request $request, $candidateTestId)
    {
        try {
            $candidateTest = \App\Models\CandidateTest::with([
                'candidate:id,name,email,position,nik,phone_number,birth_date,gender,department',
                'test:id,name,target_position',
                'discResults',
                'caasResults', 
                'telitiResults',
                'testDistribution'
            ])->findOrFail($candidateTestId);

            // Check if test is completed
            if ($candidateTest->status !== \App\Models\CandidateTest::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test is not completed yet'
                ], 400);
            }

            // Get candidate data, dengan fallback ke TestDistributionCandidate jika data tidak lengkap
            $candidate = $candidateTest->candidate;
            
            // Jika candidate data tidak lengkap (null/empty), coba ambil dari TestDistributionCandidate
            if ($candidateTest->test_distribution_id) {
                $query = \App\Models\TestDistributionCandidate::where('test_distribution_id', $candidateTest->test_distribution_id);
                
                // Cari berdasarkan email atau name jika ada
                if (!empty($candidate->email)) {
                    $query->where('email', $candidate->email);
                } elseif (!empty($candidate->name)) {
                    $query->where('name', $candidate->name);
                }
                
                $testDistributionCandidate = $query->first();
                
                if ($testDistributionCandidate) {
                    // Merge data dari TestDistributionCandidate jika candidate data kosong atau null
                    if (empty($candidate->nik) && !empty($testDistributionCandidate->nik)) {
                        $candidate->nik = $testDistributionCandidate->nik;
                    }
                    if (empty($candidate->phone_number) && !empty($testDistributionCandidate->phone_number)) {
                        $candidate->phone_number = $testDistributionCandidate->phone_number;
                    }
                    if (empty($candidate->email) && !empty($testDistributionCandidate->email)) {
                        $candidate->email = $testDistributionCandidate->email;
                    }
                    if (empty($candidate->gender) && !empty($testDistributionCandidate->gender)) {
                        $candidate->gender = $testDistributionCandidate->gender;
                    }
                    if (empty($candidate->position) && !empty($testDistributionCandidate->position)) {
                        $candidate->position = $testDistributionCandidate->position;
                    }
                }
            }

            // Get test sections to understand what results to expect
            $testSections = \App\Models\TestSection::where('test_id', $candidateTest->test_id)
                ->orderBy('sequence')
                ->get();

            $results = [
                'candidate_test' => $candidateTest,
                'test_sections' => $testSections,
                'disc_results' => $candidateTest->discResults,
                'caas_results' => $candidateTest->caasResults,
                'teliti_results' => $candidateTest->telitiResults,
            ];

            // Log activity
            LogActivityService::addToLog("Viewed test results for candidate test ID: {$candidateTestId}", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test results retrieved successfully',
                'data' => $results
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve test results: ' . $e->getMessage()
            ], 500);
        }
    }
}
