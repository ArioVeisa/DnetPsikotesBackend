<?php

namespace App\Http\Controllers;

use App\Models\CandidateTest;
use App\Models\CandidateAnswer;
use App\Models\TestSection;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Get monitoring data for all candidate tests
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTests(Request $request)
    {
        try {
            // Start building the query
            $query = CandidateTest::with([
                'candidate:id,name,nik,email',
                'test:id,name,target_position',
                'test.sections:id,test_id,section_type,duration_minutes,question_count'
            ]);

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply search
            $this->applySearch($query, $request);

            // Get pagination parameters
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            // Execute query with pagination
            $candidateTests = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform the data
            $transformedData = $candidateTests->getCollection()->map(function ($candidateTest) {
                return $this->transformCandidateTestData($candidateTest);
            });

            // Log activity
            LogActivityService::addToLog("Viewed monitoring dashboard for tests", $request);

            return response()->json([
                'success' => true,
                'message' => 'Monitoring data retrieved successfully',
                'data' => $transformedData,
                'pagination' => [
                    'current_page' => $candidateTests->currentPage(),
                    'last_page' => $candidateTests->lastPage(),
                    'per_page' => $candidateTests->perPage(),
                    'total' => $candidateTests->total(),
                    'from' => $candidateTests->firstItem(),
                    'to' => $candidateTests->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve monitoring data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            
            // Handle custom status mapping
            switch ($status) {
                case 'selesai':
                    $query->where('status', CandidateTest::STATUS_COMPLETED);
                    break;
                case 'sedang_mengerjakan':
                    $query->where('status', CandidateTest::STATUS_IN_PROGRESS);
                    break;
                case 'belum_mengerjakan':
                    $query->where('status', CandidateTest::STATUS_NOT_STARTED);
                    break;
                case 'waktu_habis':
                    // Tests that are in progress but expired
                    $query->where('status', CandidateTest::STATUS_IN_PROGRESS)
                        ->where(function ($q) {
                            $q->where('started_at', '<', now()->subHours(24)) // Assuming 24h timeout
                              ->orWhere('created_at', '<', now()->subDays(7)); // 7 days from invitation
                        });
                    break;
                default:
                    // Use the exact status value
                    $query->where('status', $status);
                    break;
            }
        }

        // Filter by test ID
        if ($request->has('test_id') && !empty($request->test_id)) {
            $query->where('test_id', $request->test_id);
        }

        // Filter by date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
    }

    /**
     * Apply search functionality
     */
    private function applySearch($query, Request $request)
    {
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            
            $query->whereHas('candidate', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('nik', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            })->orWhereHas('test', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('target_position', 'like', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Transform candidate test data for the response
     */
    private function transformCandidateTestData($candidateTest)
    {
        // Calculate progress
        $progress = $this->calculateProgress($candidateTest);
        
        // Determine status with custom logic
        $status = $this->determineStatus($candidateTest);
        
        // Calculate deadline
        $deadline = $this->calculateDeadline($candidateTest);

        return [
            'id' => $candidateTest->id,
            'candidate_name' => $candidateTest->candidate->name,
            'candidate_nik' => $candidateTest->candidate->nik,
            'candidate_email' => $candidateTest->candidate->email,
            'test_package_name' => $candidateTest->test->name,
            'target_position' => $candidateTest->test->target_position,
            'status' => $status,
            'start_time' => $candidateTest->started_at?->format('Y-m-d H:i:s'),
            'finish_time' => $candidateTest->completed_at?->format('Y-m-d H:i:s'),
            'deadline' => $deadline,
            'progress' => $progress,
            'invited_at' => $candidateTest->created_at->format('Y-m-d H:i:s'),
            'unique_token' => $candidateTest->unique_token,
        ];
    }

    /**
     * Calculate progress (answered questions / total questions)
     */
    private function calculateProgress($candidateTest)
    {
        $totalQuestions = 0;
        $answeredQuestions = 0;

        // Get total questions from test sections
        foreach ($candidateTest->test->sections as $section) {
            $totalQuestions += $section->question_count ?? 0;
        }

        // Get answered questions
        $answeredQuestions = CandidateAnswer::where('candidate_test_id', $candidateTest->id)->count();

        if ($totalQuestions > 0) {
            $percentage = round(($answeredQuestions / $totalQuestions) * 100, 1);
            return [
                'answered' => $answeredQuestions,
                'total' => $totalQuestions,
                'percentage' => $percentage,
                'display' => "{$answeredQuestions}/{$totalQuestions} soal terjawab ({$percentage}%)"
            ];
        }

        return [
            'answered' => 0,
            'total' => 0,
            'percentage' => 0,
            'display' => "0/0 soal terjawab (0%)"
        ];
    }

    /**
     * Determine the current status of the test
     */
    private function determineStatus($candidateTest)
    {
        // Check if test is expired
        if ($candidateTest->isExpired()) {
            return 'Waktu Habis';
        }

        // Check if test is in progress but time has run out
        if ($candidateTest->status === CandidateTest::STATUS_IN_PROGRESS) {
            $deadline = $this->calculateDeadline($candidateTest);
            if ($deadline && now()->gt($deadline)) {
                return 'Waktu Habis';
            }
        }

        // Map status to Indonesian
        switch ($candidateTest->status) {
            case CandidateTest::STATUS_COMPLETED:
                return 'Selesai';
            case CandidateTest::STATUS_IN_PROGRESS:
                return 'Sedang Mengerjakan';
            case CandidateTest::STATUS_NOT_STARTED:
                return 'Belum Mengerjakan';
            default:
                return ucfirst(str_replace('_', ' ', $candidateTest->status));
        }
    }

    /**
     * Calculate deadline based on test sections
     */
    private function calculateDeadline($candidateTest)
    {
        if (!$candidateTest->started_at) {
            // If not started, deadline is 7 days from invitation
            return $candidateTest->created_at->addDays(7)->format('Y-m-d H:i:s');
        }

        // Calculate total duration from test sections
        $totalDurationMinutes = 0;
        foreach ($candidateTest->test->sections as $section) {
            $totalDurationMinutes += $section->duration_minutes;
        }

        // Add buffer time (e.g., 30 minutes)
        $totalDurationMinutes += 30;

        // Calculate deadline from start time
        $deadline = $candidateTest->started_at->addMinutes($totalDurationMinutes);
        
        return $deadline->format('Y-m-d H:i:s');
    }

    /**
     * Get summary statistics for the dashboard
     */
    public function getSummary(Request $request)
    {
        try {
            $query = CandidateTest::query();

            // Apply same filters as main endpoint
            $this->applyFilters($query, $request);
            $this->applySearch($query, $request);

            $totalTests = $query->count();
            $completedTests = (clone $query)->where('status', CandidateTest::STATUS_COMPLETED)->count();
            $inProgressTests = (clone $query)->where('status', CandidateTest::STATUS_IN_PROGRESS)->count();
            $notStartedTests = (clone $query)->where('status', CandidateTest::STATUS_NOT_STARTED)->count();
            $expiredTests = (clone $query)->where(function ($q) {
                $q->where('status', CandidateTest::STATUS_IN_PROGRESS)
                  ->where('started_at', '<', now()->subHours(24))
                  ->orWhere('created_at', '<', now()->subDays(7));
            })->count();

            return response()->json([
                'success' => true,
                'message' => 'Summary statistics retrieved successfully',
                'data' => [
                    'total_tests' => $totalTests,
                    'completed_tests' => $completedTests,
                    'in_progress_tests' => $inProgressTests,
                    'not_started_tests' => $notStartedTests,
                    'expired_tests' => $expiredTests,
                    'completion_rate' => $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 1) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve summary statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
