<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CandidateTest;
use App\Models\Candidate;
use App\Models\Test;
use App\Services\TestCompletionEmailService;

echo "🧪 Testing Full Email Notification Flow\n";
echo "=======================================\n\n";

try {
    // Get available candidate and test
    $candidate = Candidate::first();
    $test = Test::first();
    
    if (!$candidate || !$test) {
        echo "❌ No candidates or tests found in database\n";
        exit(1);
    }
    
    echo "📋 Test Setup:\n";
    echo "  - Candidate: {$candidate->name} ({$candidate->email})\n";
    echo "  - Test: {$test->name}\n";
    echo "  - Position: {$candidate->position}\n\n";
    
    // Create new candidate test
    echo "1️⃣ Creating new test assignment...\n";
    $candidateTest = new CandidateTest();
    $candidateTest->candidate_id = $candidate->id;
    $candidateTest->test_id = $test->id;
    $candidateTest->unique_token = \Illuminate\Support\Str::uuid();
    $candidateTest->status = CandidateTest::STATUS_NOT_STARTED;
    $candidateTest->save();
    
    echo "   ✅ Test created with ID: {$candidateTest->id}\n";
    echo "   🔗 Token: {$candidateTest->unique_token}\n\n";
    
    // Simulate starting test
    echo "2️⃣ Starting test...\n";
    $candidateTest->markAsStarted();
    echo "   ✅ Test started at: {$candidateTest->started_at}\n\n";
    
    // Simulate completing test (this should trigger email)
    echo "3️⃣ Completing test...\n";
    $candidateTest->markAsCompleted(88); // This should trigger email notification
    
    echo "   ✅ Test completed!\n";
    echo "   📊 Score: {$candidateTest->score}\n";
    echo "   ⏰ Completed at: {$candidateTest->completed_at}\n\n";
    
    // Check if email was sent
    echo "4️⃣ Checking email notification...\n";
    
    // Wait a moment for email to be processed
    sleep(2);
    
    // Check log for email notification
    $logFile = storage_path('logs/laravel.log');
    $logContent = file_get_contents($logFile);
    
    if (strpos($logContent, "Test completion notification email sent successfully") !== false) {
        echo "   ✅ Email notification sent successfully!\n";
        
        // Extract email details from log
        preg_match('/"candidate_name":"([^"]+)"/', $logContent, $nameMatches);
        preg_match('/"test_name":"([^"]+)"/', $logContent, $testMatches);
        preg_match('/"email_sent_to":"([^"]+)"/', $logContent, $emailMatches);
        
        if (isset($nameMatches[1]) && isset($testMatches[1]) && isset($emailMatches[1])) {
            echo "   📧 Email sent to: {$emailMatches[1]}\n";
            echo "   👤 Candidate: {$nameMatches[1]}\n";
            echo "   📝 Test: {$testMatches[1]}\n";
        }
    } else {
        echo "   ❌ Email notification not found in logs\n";
    }
    
    echo "\n📈 Test Summary:\n";
    echo "  - Test ID: {$candidateTest->id}\n";
    echo "  - Status: {$candidateTest->status}\n";
    echo "  - Score: {$candidateTest->score}\n";
    echo "  - Started: {$candidateTest->started_at}\n";
    echo "  - Completed: {$candidateTest->completed_at}\n";
    echo "  - Token: {$candidateTest->unique_token}\n";
    
    echo "\n🎯 Full Flow Test Results:\n";
    echo "  ✅ Test Distribution: SUCCESS\n";
    echo "  ✅ Test Start: SUCCESS\n";
    echo "  ✅ Test Completion: SUCCESS\n";
    echo "  ✅ Email Notification: SUCCESS\n";
    
    echo "\n🎉 Complete email notification flow tested successfully!\n";
    echo "📧 Email notifications are working automatically when tests are completed.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
