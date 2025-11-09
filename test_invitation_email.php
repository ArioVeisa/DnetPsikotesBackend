<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Candidate;
use App\Models\CandidateTest;
use App\Models\Test;
use App\Models\TestDistribution;
use App\Mail\TestInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

echo "🧪 Testing Email Invitation\n";
echo "==========================\n\n";

try {
    // Test email destination
    $testEmail = 'arioveisa@gmail.com';
    
    echo "📧 Target Email: {$testEmail}\n\n";
    
    // Get or create test data
    echo "1️⃣ Preparing test data...\n";
    
    // Get first test or create dummy
    $test = Test::first();
    if (!$test) {
        echo "   ⚠️  No test found, creating dummy test...\n";
        $test = Test::create([
            'name' => 'Tes Psikologi Online',
            'description' => 'Tes psikologi untuk rekrutmen',
            'duration_minutes' => 70, // 1 hour 10 mins
            'instructions' => 'Ikuti instruksi dengan baik',
        ]);
    }
    
    echo "   ✅ Test: {$test->name} (Duration: {$test->duration_minutes} minutes)\n";
    
    // Get or create candidate
    $candidate = Candidate::where('email', $testEmail)->first();
    if (!$candidate) {
        echo "   ⚠️  Candidate not found, creating dummy candidate...\n";
        $candidate = Candidate::create([
            'name' => 'Ida Ayu Kade',
            'email' => $testEmail,
            'nik' => '1234567890',
            'phone_number' => '081234567890',
            'position' => 'Staff',
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
            'gender' => 'female',
            'department' => 'IT',
        ]);
    }
    
    echo "   ✅ Candidate: {$candidate->name} ({$candidate->email})\n";
    
    // Get or create test distribution
    $testDistribution = TestDistribution::first();
    if (!$testDistribution) {
        echo "   ⚠️  Test distribution not found, creating dummy test distribution...\n";
        $testDistribution = TestDistribution::create([
            'name' => 'Test Distribution Test',
            'test_id' => $test->id,
            'started_date' => now()->addDays(1),
            'ended_date' => now()->addDays(2),
        ]);
    } else {
        // Update dates untuk test
        $testDistribution->update([
            'started_date' => now()->addDays(1),
            'ended_date' => now()->addDays(2),
        ]);
    }
    
    echo "   ✅ Test Distribution: {$testDistribution->name}\n";
    echo "      Start: " . ($testDistribution->started_date ? $testDistribution->started_date->format('d M Y, H:i') : 'N/A') . "\n";
    echo "      End: " . ($testDistribution->ended_date ? $testDistribution->ended_date->format('d M Y, H:i') : 'N/A') . "\n\n";
    
    // Create candidate test
    echo "2️⃣ Creating candidate test...\n";
    
    // Delete existing candidate test untuk test ini
    CandidateTest::where('candidate_id', $candidate->id)
        ->where('test_id', $test->id)
        ->delete();
    
    $candidateTest = CandidateTest::create([
        'candidate_id' => $candidate->id,
        'test_id' => $test->id,
        'test_distribution_id' => $testDistribution->id,
        'unique_token' => (string) Str::uuid(),
        'status' => CandidateTest::STATUS_NOT_STARTED,
    ]);
    
    echo "   ✅ Candidate Test ID: {$candidateTest->id}\n";
    echo "   🔗 Token: {$candidateTest->unique_token}\n";
    echo "   🔗 Test Link: " . env('FRONTEND_URL', 'https://gertude-uncategorised-laurene.ngrok-free.dev') . "/test/{$candidateTest->unique_token}\n\n";
    
    // Send email
    echo "3️⃣ Sending email invitation...\n";
    
    try {
        $startTime = microtime(true);
        
        Mail::to($testEmail)->send(new TestInvitationMail(
            $candidate,
            $candidateTest,
            $test,
            null // custom message
        ));
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        echo "   ✅ Email sent successfully! (took {$duration}s)\n";
        echo "   📧 Email sent to: {$testEmail}\n";
        echo "   👤 Candidate: {$candidate->name}\n";
        echo "   📝 Test: {$test->name}\n";
        echo "   ⏱️  Duration: " . floor($test->duration_minutes / 60) . " jam " . ($test->duration_minutes % 60) . " menit\n";
        
        if ($testDistribution->started_date && $testDistribution->ended_date) {
            echo "   📅 Valid from: " . $testDistribution->started_date->locale('id')->translatedFormat('d M Y, h:i A') . "\n";
            echo "   📅 Valid until: " . $testDistribution->ended_date->locale('id')->translatedFormat('d M Y, h:i A') . "\n";
        }
        
        echo "\n🎉 Email invitation test completed successfully!\n";
        echo "📧 Please check your inbox at {$testEmail}\n";
        
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        echo "   ❌ Email transport error: " . $e->getMessage() . "\n";
        echo "   💡 Make sure SMTP is configured correctly in .env file\n";
        exit(1);
    } catch (\Exception $e) {
        echo "   ❌ Error sending email: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
