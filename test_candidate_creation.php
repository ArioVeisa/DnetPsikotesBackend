<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\CandidateController;
use App\Models\Candidate;

echo "🧪 Testing candidate creation...\n";

try {
    // Test 1: Direct model creation
    echo "\n1. Testing direct model creation...\n";
    
    $candidate = Candidate::create([
        'nik' => '1234567890123456',
        'name' => 'Test User Direct',
        'email' => 'test-direct@example.com',
        'phone_number' => '081234567890',
        'position' => 'Staff',
        'birth_date' => '1990-01-01',
        'gender' => 'female',
        'department' => 'HRD'
    ]);
    
    echo "✅ Direct model creation successful: {$candidate->name} ({$candidate->gender})\n";
    
    // Clean up
    $candidate->delete();
    echo "✅ Test data cleaned up\n";
    
    // Test 2: Controller method
    echo "\n2. Testing controller method...\n";
    
    $controller = new CandidateController();
    $request = new Request([
        'nik' => '1234567890123457',
        'name' => 'Test User Controller',
        'email' => 'test-controller@example.com',
        'phone_number' => '081234567891',
        'position' => 'Manager',
        'birth_date' => '1991-01-01',
        'gender' => 'female',
        'department' => 'IT'
    ]);
    
    $response = $controller->store($request);
    
    echo "✅ Controller method successful\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
    // Test 3: Validation error
    echo "\n3. Testing validation error...\n";
    
    $requestInvalid = new Request([
        'nik' => '', // Empty NIK
        'name' => 'Test User Invalid',
        'email' => 'invalid-email', // Invalid email
        'phone_number' => '081234567892',
        'position' => 'Staff',
        'birth_date' => '1992-01-01',
        'gender' => 'female',
        'department' => 'HRD'
    ]);
    
    $responseInvalid = $controller->store($requestInvalid);
    
    echo "✅ Validation error test successful\n";
    echo "Response status: " . $responseInvalid->getStatusCode() . "\n";
    echo "Response content: " . $responseInvalid->getContent() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎉 All tests completed!\n";
