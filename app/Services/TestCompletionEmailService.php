<?php

namespace App\Services;

use App\Models\CandidateTest;
use App\Models\Candidate;
use App\Models\Test;
use App\Mail\TestCompletionNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestCompletionEmailService
{
    /**
     * Send test completion notification email
     */
    public function sendCompletionNotification(CandidateTest $candidateTest): bool
    {
        try {
            // Get candidate and test data
            $candidate = $candidateTest->candidate;
            $test = $candidateTest->test;

            if (!$candidate || !$test) {
                Log::error('TestCompletionEmailService: Missing candidate or test data', [
                    'candidate_test_id' => $candidateTest->id,
                    'candidate_id' => $candidateTest->candidate_id,
                    'test_id' => $candidateTest->test_id,
                ]);
                return false;
            }

            // Generate result link (adjust this URL according to your frontend routes)
            $resultLink = $this->generateResultLink($candidateTest);

            // Prepare email data
            $emailData = [
                'candidateName' => $candidate->name,
                'candidateEmail' => $candidate->email,
                'candidatePosition' => $candidate->position,
                'testName' => $test->name,
                'targetPosition' => $test->target_position,
                'score' => $candidateTest->score ?? 0,
                'completedAt' => $candidateTest->completed_at->format('d F Y, H:i'),
                'resultLink' => $resultLink,
            ];

            // Send email to configured notification email
            Mail::to(env('TEST_COMPLETION_NOTIFICATION_EMAIL', 'arioveisa@gmail.com'))
                ->send(new TestCompletionNotification(
                    $emailData['candidateName'],
                    $emailData['candidateEmail'],
                    $emailData['candidatePosition'],
                    $emailData['testName'],
                    $emailData['targetPosition'],
                    $emailData['score'],
                    $emailData['completedAt'],
                    $emailData['resultLink']
                ));

            Log::info('Test completion notification email sent successfully', [
                'candidate_test_id' => $candidateTest->id,
                'candidate_name' => $candidate->name,
                'test_name' => $test->name,
                'email_sent_to' => env('TEST_COMPLETION_NOTIFICATION_EMAIL', 'arioveisa@gmail.com'),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send test completion notification email', [
                'candidate_test_id' => $candidateTest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Generate result link for the completed test
     */
    private function generateResultLink(CandidateTest $candidateTest): string
    {
        // Adjust this URL according to your frontend application URL
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        
        // Generate a secure link to view the test results
        // You might want to create a secure token or use the unique_token
        $token = $candidateTest->unique_token;
        
        return "{$frontendUrl}/results/{$candidateTest->id}?token={$token}";
    }

    /**
     * Send completion notification for multiple tests
     */
    public function sendBulkCompletionNotifications(array $candidateTestIds): array
    {
        $results = [];
        
        foreach ($candidateTestIds as $testId) {
            $candidateTest = CandidateTest::find($testId);
            
            if ($candidateTest && $candidateTest->status === CandidateTest::STATUS_COMPLETED) {
                $results[$testId] = $this->sendCompletionNotification($candidateTest);
            } else {
                $results[$testId] = false;
                Log::warning('Skipping email notification - test not completed', [
                    'candidate_test_id' => $testId,
                    'status' => $candidateTest?->status,
                ]);
            }
        }

        return $results;
    }
}
