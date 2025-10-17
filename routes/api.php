<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankSoal\CaasCategoryController;
use App\Http\Controllers\BankSoal\CaasController;
use App\Http\Controllers\BankSoal\DiscCategoryController;
use App\Http\Controllers\BankSoal\DiscController;
use App\Http\Controllers\BankSoal\TelitiCategoryController;
use App\Http\Controllers\BankSoal\TelitiController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ManajemenTes\SectionController;
use App\Http\Controllers\ManajemenTes\TestController;
use App\Http\Controllers\ManajemenTes\TestQuestionController;
use App\Http\Controllers\ManajemenTes\TestTemplateController;
use App\Http\Controllers\Results\CaasResultController;
use App\Http\Controllers\Results\DiscResultController;
use App\Http\Controllers\Results\ResultController;
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

// Endpoint yang butuh autentikasi (token JWT)
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

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

    Route::apiResource('candidates', CandidateController::class);

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

        // Result Test
        Route::apiResource('candidate-tests', ResultController::class);
        Route::get('candidate-tests/{candidate_test_id}/download', [ResultController::class, 'download']);
        Route::apiResource('teliti-results', TelitiResultController::class);
        Route::apiResource('caas-results', CaasResultController::class);
        Route::apiResource('disc-results', DiscResultController::class);
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
