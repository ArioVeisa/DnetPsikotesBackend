<?php

namespace App\Http\Controllers;

use App\Models\CandidateTest;
use App\Services\TestCompletionEmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestEmailController extends Controller
{
    protected $emailService;

    public function __construct(TestCompletionEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send test completion email for a specific candidate test
     */
    public function sendTestCompletionEmail(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_test_id' => 'required|exists:candidate_tests,id'
        ]);

        try {
            $candidateTest = CandidateTest::with(['candidate', 'test'])
                ->findOrFail($request->candidate_test_id);

            // Check if test is completed
            if ($candidateTest->status !== CandidateTest::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test belum selesai. Email hanya bisa dikirim untuk tes yang sudah completed.'
                ], 400);
            }

            // Send email
            $result = $this->emailService->sendCompletionNotification($candidateTest);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email notifikasi berhasil dikirim ke ' . env('TEST_COMPLETION_NOTIFICATION_EMAIL', 'arioveisa@gmail.com'),
                    'data' => [
                        'candidate_name' => $candidateTest->candidate->name,
                        'test_name' => $candidateTest->test->name,
                        'score' => $candidateTest->score,
                        'completed_at' => $candidateTest->completed_at,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email notifikasi'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test completion email for all completed tests (bulk)
     */
    public function sendBulkTestCompletionEmails(Request $request): JsonResponse
    {
        try {
            // Get all completed tests that haven't been sent email yet
            // For now, we'll send to all completed tests
            $completedTests = CandidateTest::with(['candidate', 'test'])
                ->where('status', CandidateTest::STATUS_COMPLETED)
                ->get();

            if ($completedTests->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tes yang sudah selesai'
                ], 404);
            }

            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($completedTests as $candidateTest) {
                $result = $this->emailService->sendCompletionNotification($candidateTest);
                $results[] = [
                    'candidate_test_id' => $candidateTest->id,
                    'candidate_name' => $candidateTest->candidate->name,
                    'test_name' => $candidateTest->test->name,
                    'email_sent' => $result
                ];

                if ($result) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Email notifikasi berhasil dikirim: {$successCount} sukses, {$failCount} gagal",
                'data' => [
                    'total_tests' => $completedTests->count(),
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email functionality with dummy data
     */
    public function testEmailFunctionality(Request $request): JsonResponse
    {
        try {
            // Create a dummy candidate test for testing
            $testData = [
                'candidateName' => 'Doni Test',
                'candidateEmail' => 'doni.test@example.com',
                'candidatePosition' => 'Software Engineer',
                'testName' => 'Tes Rekrutmen IT',
                'targetPosition' => 'IT',
                'score' => 85,
                'completedAt' => now()->format('d F Y, H:i'),
                'resultLink' => 'http://localhost:3000/results/1?token=test-token-123',
            ];

            // Send test email directly
            \Mail::to(env('TEST_COMPLETION_NOTIFICATION_EMAIL', 'arioveisa@gmail.com'))
                ->send(new \App\Mail\TestCompletionNotification(
                    $testData['candidateName'],
                    $testData['candidateEmail'],
                    $testData['candidatePosition'],
                    $testData['testName'],
                    $testData['targetPosition'],
                    $testData['score'],
                    $testData['completedAt'],
                    $testData['resultLink']
                ));

            return response()->json([
                'success' => true,
                'message' => 'Test email berhasil dikirim ke ' . env('TEST_COMPLETION_NOTIFICATION_EMAIL', 'arioveisa@gmail.com'),
                'data' => $testData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
