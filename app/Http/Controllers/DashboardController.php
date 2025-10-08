<?php

namespace App\Http\Controllers;

use App\Models\CandidateTest;
use App\Models\Candidate;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get comprehensive dashboard data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request)
    {
        try {
            // Get test statistics
            $testStats = $this->getTestStatistics();
            
            // Get user statistics
            $userStats = $this->getUserStatistics();
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Log activity
            LogActivityService::addToLog("Viewed dashboard data", $request);

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'summary' => [
                        'total_tests_completed' => $testStats['completed'],
                        'total_tests_in_progress' => $testStats['in_progress'],
                        'total_tests_pending' => $testStats['pending'],
                        'completion_rate' => $testStats['completion_rate'],
                        'total_candidates' => $userStats['total_candidates'],
                        'total_hrd_users' => $userStats['total_hrd_users'],
                    ],
                    'recent_activities' => $recentActivities
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test statistics
     */
    private function getTestStatistics()
    {
        $totalTests = CandidateTest::count();
        $completedTests = CandidateTest::where('status', CandidateTest::STATUS_COMPLETED)->count();
        $inProgressTests = CandidateTest::where('status', CandidateTest::STATUS_IN_PROGRESS)->count();
        $pendingTests = CandidateTest::where('status', CandidateTest::STATUS_NOT_STARTED)->count();
        
        // Calculate completion rate
        $completionRate = $totalTests > 0 ? round(($completedTests / $totalTests) * 100, 1) : 0;

        return [
            'completed' => $completedTests,
            'in_progress' => $inProgressTests,
            'pending' => $pendingTests,
            'completion_rate' => $completionRate . '%'
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        $totalCandidates = Candidate::count();
        $totalHrdUsers = User::whereIn('role', ['admin', 'super_admin'])->count();

        return [
            'total_candidates' => $totalCandidates,
            'total_hrd_users' => $totalHrdUsers
        ];
    }

    /**
     * Get recent activities (last 5) - hanya untuk tes yang sudah selesai
     */
    private function getRecentActivities()
    {
        // Ambil CandidateTest yang sudah completed dengan relasi candidate dan test
        $completedTests = CandidateTest::with(['candidate:id,name', 'test:id,name'])
            ->where('status', CandidateTest::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(5)
            ->get();

        return $completedTests->map(function ($candidateTest) {
            $candidateName = $candidateTest->candidate ? $candidateTest->candidate->name : 'Unknown Candidate';
            $testName = $candidateTest->test ? $candidateTest->test->name : 'Unknown Test';
            
            return [
                'user_name' => $candidateName,
                'description' => "Menyelesaikan tes: {$testName}",
                'timestamp' => $candidateTest->completed_at->format('Y-m-d H:i:s'),
                'status' => 'completed'
            ];
        })->toArray();
    }

    /**
     * Get detailed test statistics with additional metrics
     */
    public function getDetailedTestStats(Request $request)
    {
        try {
            $stats = $this->getTestStatistics();
            $userStats = $this->getUserStatistics();
            
            // Add additional metrics
            $expiredTests = CandidateTest::where(function ($query) {
                $query->where('status', CandidateTest::STATUS_IN_PROGRESS)
                      ->where('started_at', '<', now()->subHours(24))
                      ->orWhere('created_at', '<', now()->subDays(7));
            })->count();

            // Get tests by status with percentages
            $totalTests = CandidateTest::count();
            $stats['expired'] = $expiredTests;
            $stats['total'] = $totalTests;
            
            if ($totalTests > 0) {
                $stats['completed_percentage'] = round(($stats['completed'] / $totalTests) * 100, 1);
                $stats['in_progress_percentage'] = round(($stats['in_progress'] / $totalTests) * 100, 1);
                $stats['pending_percentage'] = round(($stats['pending'] / $totalTests) * 100, 1);
                $stats['expired_percentage'] = round(($expiredTests / $totalTests) * 100, 1);
            } else {
                $stats['completed_percentage'] = 0;
                $stats['in_progress_percentage'] = 0;
                $stats['pending_percentage'] = 0;
                $stats['expired_percentage'] = 0;
            }

            // Get recent test activity (last 7 days)
            $recentTestActivity = CandidateTest::where('created_at', '>=', now()->subDays(7))
                ->count();

            $stats['recent_activity'] = $recentTestActivity;

            LogActivityService::addToLog("Viewed detailed test statistics", $request);

            return response()->json([
                'success' => true,
                'message' => 'Detailed test statistics retrieved successfully',
                'data' => [
                    'test_statistics' => $stats,
                    'user_statistics' => $userStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve detailed test statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard charts data
     */
    public function getChartsData(Request $request)
    {
        try {
            // Get test completion trend (last 30 days)
            $completionTrend = CandidateTest::select(
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', CandidateTest::STATUS_COMPLETED)
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            // Get tests by status distribution
            $statusDistribution = CandidateTest::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                $statusMap = [
                    CandidateTest::STATUS_COMPLETED => 'completed',
                    CandidateTest::STATUS_IN_PROGRESS => 'in_progress',
                    CandidateTest::STATUS_NOT_STARTED => 'pending'
                ];
                return [$statusMap[$item->status] ?? $item->status => $item->count];
            });

            // Get candidate registration trend (last 30 days)
            $candidateTrend = Candidate::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            LogActivityService::addToLog("Viewed dashboard charts data", $request);

            return response()->json([
                'success' => true,
                'message' => 'Charts data retrieved successfully',
                'data' => [
                    'completion_trend' => $completionTrend,
                    'status_distribution' => $statusDistribution,
                    'candidate_trend' => $candidateTrend
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve charts data: ' . $e->getMessage()
            ], 500);
        }
    }
}
