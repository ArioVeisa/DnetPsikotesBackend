<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankSoal\CaasCategoryController;
use App\Http\Controllers\BankSoal\CaasController;
use App\Http\Controllers\BankSoal\DiscCategoryController;
use App\Http\Controllers\BankSoal\DiscController;
use App\Http\Controllers\BankSoal\TelitiCategoryController;
use App\Http\Controllers\BankSoal\TelitiController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ManajemenTes\SectionController;
use App\Http\Controllers\ManajemenTes\TestController;
use App\Http\Controllers\ManajemenTes\TestQuestionController;
use App\Http\Controllers\ManajemenTes\TestTemplateController;
use App\Http\Controllers\Results\CaasResultController;
use App\Http\Controllers\Results\DiscResultController;
use App\Http\Controllers\TestAccessController;
use App\Http\Controllers\TestDistributionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Results\TelitiResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


// Test routes (no authentication required)
Route::post('/test/teliti-questions', [\App\Http\Controllers\BankSoal\TelitiController::class, 'store']);
Route::get('/test/teliti-questions', [\App\Http\Controllers\BankSoal\TelitiController::class, 'index']);

// Endpoint publik untuk login
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);

// Public routes (no auth required as they use tokens)
Route::get('candidate-tests/start/{token}', [TestDistributionController::class, 'startTest'])
    ->name('candidate-tests.start');
Route::post('/candidate-tests/submit/{token}', [TestDistributionController::class, 'submitTest'])
    ->name('candidate-tests.submit');
Route::get('candidate-tests/validate/{token}', [TestDistributionController::class, 'validateBeforeSubmit'])
    ->name('candidate-tests.validate');

// Test endpoint without authentication
Route::get('test-results', [App\Http\Controllers\ResultsController::class, 'index']);
Route::get('results-public', [App\Http\Controllers\ResultsController::class, 'index']);
Route::get('candidate-tests-public/{candidateTestId}/results', [App\Http\Controllers\TestDistributionController::class, 'getResults']);
Route::get('activity-logs-public', [App\Http\Controllers\ActivityLogController::class, 'index']);
Route::get('dashboard-public', [App\Http\Controllers\DashboardController::class, 'getDashboard']);
Route::get('test-distributions-public', [App\Http\Controllers\TestDistributionController::class, 'getActiveDistributions']);
Route::delete('test-distributions-public/{testId}', [App\Http\Controllers\TestDistributionController::class, 'deleteDistribution']);
Route::post('test-distributions-public/create-from-package', [App\Http\Controllers\TestDistributionController::class, 'createDistributionFromPackage']);
Route::delete('results-public/delete/{candidateTestId}', [App\Http\Controllers\TestDistributionController::class, 'deleteResult']);
Route::get('candidates-public', [App\Http\Controllers\CandidateController::class, 'index']);
Route::get('tests-public/{testId}/with-sections', [App\Http\Controllers\ManajemenTes\TestQuestionController::class, 'showTestWithSections']);
Route::get('debug/test-questions/{testId}', function($testId) {
    $sections = \App\Models\TestSection::with('testQuestions')->where('test_id', $testId)->get();
    return response()->json([
        'test_id' => $testId,
        'sections_count' => $sections->count(),
        'sections' => $sections->map(function($section) {
            return [
                'section_id' => $section->id,
                'section_type' => $section->section_type,
                'questions_count' => $section->testQuestions->count(),
                'questions' => $section->testQuestions->map(function($q) {
                    return [
                        'id' => $q->id,
                        'question_id' => $q->question_id,
                        'question_type' => $q->question_type,
                        'has_detail' => $q->question_detail !== null
                    ];
                })
            ];
        })
    ]);
});

Route::get('debug/all-tests', function() {
    $tests = \App\Models\Test::with('sections.testQuestions')->get();
    return response()->json([
        'tests_count' => $tests->count(),
        'tests' => $tests->map(function($test) {
            return [
                'id' => $test->id,
                'name' => $test->name,
                'sections_count' => $test->sections->count(),
                'total_questions' => $test->sections->sum(function($section) {
                    return $section->testQuestions->count();
                }),
                'sections' => $test->sections->map(function($section) {
                    return [
                        'section_id' => $section->id,
                        'section_type' => $section->section_type,
                        'questions_count' => $section->testQuestions->count()
                    ];
                })
            ];
        })
    ]);
});

Route::post('debug/add-question', function(\Illuminate\Http\Request $request) {
    try {
        \Log::info("Debug add-question called with: " . json_encode($request->all()));
        
        $data = $request->validate([
            'test_id' => 'required|integer',
            'section_id' => 'required|integer', 
            'question_id' => 'required|integer',
            'question_type' => 'required|string'
        ]);
        
        \Log::info("Validated data: " . json_encode($data));
        
        // Check if question exists in respective table
        $exists = match ($data['question_type']) {
            'CAAS' => \App\Models\CaasQuestion::where('id', $data['question_id'])->exists(),
            'DISC' => \App\Models\DiscQuestion::where('id', $data['question_id'])->exists(),
            'teliti' => \App\Models\TelitiQuestion::where('id', $data['question_id'])->exists(),
            default => false
        };
        
        if (!$exists) {
            return response()->json(['error' => 'Question not found in ' . $data['question_type'] . ' table'], 404);
        }
        
        $testQuestion = \App\Models\TestQuestion::create($data);
        \Log::info("Created TestQuestion: " . json_encode($testQuestion->toArray()));
        
        return response()->json(['success' => true, 'data' => $testQuestion]);
        
    } catch (\Exception $e) {
        \Log::error("Debug add-question error: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('debug/add-questions-bulk', function(\Illuminate\Http\Request $request) {
    try {
        \Log::info("Debug add-questions-bulk called with: " . json_encode($request->all()));
        
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.test_id' => 'required|integer',
            'questions.*.section_id' => 'required|integer', 
            'questions.*.question_id' => 'required|integer',
            'questions.*.question_type' => 'required|string'
        ]);
        
        $savedQuestions = [];
        foreach ($validated['questions'] as $questionData) {
            $exists = match ($questionData['question_type']) {
                'CAAS' => \App\Models\CaasQuestion::where('id', $questionData['question_id'])->exists(),
                'DISC' => \App\Models\DiscQuestion::where('id', $questionData['question_id'])->exists(),
                'teliti' => \App\Models\TelitiQuestion::where('id', $questionData['question_id'])->exists(),
                default => false
            };
            
            if (!$exists) {
                return response()->json(['error' => 'Question not found in ' . $questionData['question_type'] . ' table'], 404);
            }
            
            $testQuestion = \App\Models\TestQuestion::create($questionData);
            \Log::info("Created TestQuestion: " . json_encode($testQuestion->toArray()));
            $savedQuestions[] = $testQuestion;
        }
        
        return response()->json(['success' => true, 'data' => $savedQuestions]);
        
    } catch (\Exception $e) {
        \Log::error("Debug add-questions-bulk error: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Endpoint debug untuk frontend tanpa auth
Route::post('debug/manage-questions', function(\Illuminate\Http\Request $request) {
    try {
        \Log::info("Debug manage-questions called with: " . json_encode($request->all()));
        
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.test_id' => 'required|integer',
            'questions.*.section_id' => 'required|integer', 
            'questions.*.question_id' => 'required|integer',
            'questions.*.question_type' => 'required|string'
        ]);
        
        $savedQuestions = [];
        foreach ($validated['questions'] as $questionData) {
            $exists = match ($questionData['question_type']) {
                'CAAS' => \App\Models\CaasQuestion::where('id', $questionData['question_id'])->exists(),
                'DISC' => \App\Models\DiscQuestion::where('id', $questionData['question_id'])->exists(),
                'teliti' => \App\Models\TelitiQuestion::where('id', $questionData['question_id'])->exists(),
                default => false
            };
            
            if (!$exists) {
                return response()->json(['error' => 'Question not found in ' . $questionData['question_type'] . ' table'], 404);
            }
            
            $testQuestion = \App\Models\TestQuestion::create($questionData);
            \Log::info("Created TestQuestion: " . json_encode($testQuestion->toArray()));
            $savedQuestions[] = $testQuestion;
        }
        
        return response()->json([
            'data' => $savedQuestions,
            'status' => 'success',
            'message' => 'Questions added to test successfully'
        ], 201);
        
    } catch (\Exception $e) {
        \Log::error("Debug manage-questions error: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
Route::post('candidate-tests-public/invite', [App\Http\Controllers\TestDistributionController::class, 'inviteCandidates']);
Route::get('test-packages-public', [App\Http\Controllers\ManajemenTes\TestController::class, 'index']);

// Test email functionality (public for testing)
Route::post('test-email-public', [App\Http\Controllers\TestEmailController::class, 'testEmailFunctionality']);

// Endpoint yang butuh autentikasi (token JWT)
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    
    // Dashboard endpoint
    Route::get('dashboard', [DashboardController::class, 'getDashboard']);
    
    // Test history endpoint
    Route::get('test-history', [TestDistributionController::class, 'getTestHistory'])
        ->middleware('role:super_admin,admin');
    
    // Test distributions endpoint
    Route::get('test-distributions', [TestDistributionController::class, 'getActiveDistributions'])
        ->middleware('role:super_admin,admin');
    
    // Email notification routes
    Route::post('send-test-completion-email', [App\Http\Controllers\TestEmailController::class, 'sendTestCompletionEmail']);
    Route::post('send-bulk-test-completion-emails', [App\Http\Controllers\TestEmailController::class, 'sendBulkTestCompletionEmails']);
    Route::post('test-email-functionality', [App\Http\Controllers\TestEmailController::class, 'testEmailFunctionality']);

    // Route untuk User Management (Hanya bisa diakses Super Admin)
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Hanya bisa diakses oleh super_admin dan admin
    Route::get('manage-tests', [TestAccessController::class, 'manageTests'])
        ->middleware('role:super_admin,admin');

    // Bisa diakses oleh semua role yang login
    Route::get('view-reports', [TestAccessController::class, 'viewReports'])
        ->middleware('role:super_admin,admin,kandidat');

    Route::get('/test/{token}', [TestAccessController::class, 'show'])
        ->name('test.access');

    // Hanya bisa diakses oleh super_admin dan admin
    Route::get('candidates', [CandidateController::class, 'index'])
        ->middleware('role:super_admin,admin');
    
    // Endpoint untuk mendapatkan kandidat yang tersedia untuk test distribution
    Route::get('candidates/available', [CandidateController::class, 'getAvailableCandidates'])
        ->middleware('role:super_admin,admin');
    
    // Endpoint untuk load existing candidates yang belum pernah test
    Route::get('candidates/load-existing', [CandidateController::class, 'loadExistingCandidates'])
        ->middleware('role:super_admin,admin');
    Route::get('candidates/test-distribution-candidates', [CandidateController::class, 'getTestDistributionCandidates'])
        ->middleware('role:super_admin,admin');
    Route::post('candidates/add-to-test-distribution', [CandidateController::class, 'addToTestDistribution'])
        ->middleware('role:super_admin,admin');
    Route::post('candidates/remove-from-test-distribution', [CandidateController::class, 'removeFromTestDistribution'])
        ->middleware('role:super_admin,admin');

    // Import/Export candidates - harus didefinisikan sebelum resource route
    Route::get('candidates/template', [CandidateController::class, 'downloadTemplate'])
        ->middleware('role:super_admin,admin');
    Route::post('candidates/import', [CandidateController::class, 'import'])
        ->middleware('role:super_admin,admin');

    Route::apiResource('candidates', CandidateController::class)
        ->middleware('role:super_admin,admin');

    Route::middleware('role:super_admin,admin')->group(function () {
        // Bank Soal
        Route::apiResource('caas-categories', CaasCategoryController::class);
        Route::apiResource('caas-questions', CaasController::class);
        Route::post('/caas-questions/import', [CaasController::class, 'import']);

        Route::apiResource('teliti-categories', TelitiCategoryController::class);
        Route::apiResource('teliti-questions', TelitiController::class);
        Route::post('/teliti-questions/import', [TelitiController::class, 'import']);

        Route::apiResource('disc-categories', DiscCategoryController::class);
        Route::apiResource('disc-questions', DiscController::class);
        Route::post('/disc-questions/import', [DiscController::class, 'import']);

        // Manajemen Tes
       Route::delete('/manage-questions/{section_id}/{id}', [TestQuestionController::class, 'deleteBySection']);
        Route::apiResource('test-package', TestController::class);
        Route::apiResource('manage-questions', TestQuestionController::class);
        Route::get('manage-questions/section/{id}', [TestQuestionController::class, 'showSection']);
        Route::get('/tests/{testId}/with-sections', [TestQuestionController::class, 'showTestWithSections']); // route baru
        Route::post('/test-package/{id}/duplicate', [TestController::class, 'duplicate']);

        Route::post('/candidate-tests/invite', [TestDistributionController::class, 'inviteCandidates']);
        Route::post('/candidate-tests/resend-invitations', [TestDistributionController::class, 'resendInvitations']);
        Route::get('/candidate-tests', [TestDistributionController::class, 'getAllCandidateTests']);
        Route::delete('/test-distributions/{testId}', [TestDistributionController::class, 'deleteDistribution']);
        Route::post('/test-distributions/create-from-package', [TestDistributionController::class, 'createDistributionFromPackage']);

        // Result Test
        Route::apiResource('teliti-results', TelitiResultController::class);
        Route::apiResource('caas-results', CaasResultController::class);
        Route::apiResource('disc-results', DiscResultController::class);
        
        // Combined Results endpoint for Results page
        Route::get('results', [App\Http\Controllers\ResultsController::class, 'index']);
        Route::get('results/{id}', [App\Http\Controllers\ResultsController::class, 'show']);
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::post('/candidate-tests/{candidateTest}/resend', [TestDistributionController::class, 'resendInvitation'])
            ->middleware('can:resend_test_invitations');
        Route::get('/candidate-tests/{candidateTest}/results', [TestDistributionController::class, 'getResults'])
            ->middleware('can:view_test_results');
    });

    // Konfigurasi Tes Legacy - FR-010 & FR-011
    Route::prefix('test-templates')->group(function () {
        Route::get('/', [TestTemplateController::class, 'index']);
        Route::post('/', [TestTemplateController::class, 'store']);
        Route::get('/{id}', [TestTemplateController::class, 'show']);
        Route::put('/{id}', [TestTemplateController::class, 'update']);
        Route::delete('/{id}', [TestTemplateController::class, 'destroy']);
    });

    // Random Questions Routes - FR-011
    Route::prefix('random-questions')->group(function () {
        Route::get('/disc', [TestTemplateController::class, 'getRandomQuestions'])->name('random.disc');
        Route::get('/caas', [TestTemplateController::class, 'getRandomQuestions'])->name('random.caas');
        Route::get('/teliti', [TestTemplateController::class, 'getRandomQuestions'])->name('random.teliti');
    });
});
