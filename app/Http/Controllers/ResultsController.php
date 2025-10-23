<?php

namespace App\Http\Controllers;

use App\Models\CandidateTest;
use App\Models\DiscResult;
use App\Models\CaasResult;
use App\Models\TelitiResult;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    /**
     * Get all test results for the Results page
     */
    public function index(Request $request)
    {
        try {
            // Get all completed candidate tests with their results
            $candidateTests = CandidateTest::with([
                'candidate:id,name,email,position',
                'test:id,name,target_position',
                'discResults',
                'caasResults', 
                'telitiResults'
            ])
            ->where('status', CandidateTest::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

            // Transform data for frontend
            $results = $candidateTests->map(function ($candidateTest) {
                return [
                    'id' => $candidateTest->id,
                    'candidate_name' => $candidateTest->candidate->name,
                    'candidate_email' => $candidateTest->candidate->email,
                    'position' => $candidateTest->candidate->position ?? $candidateTest->test->target_position,
                    'test_name' => $candidateTest->test->name,
                    'completed_at' => $candidateTest->completed_at->format('Y-m-d H:i:s'),
                    'score' => $candidateTest->score,
                    'status' => 'completed',
                    'results' => [
                        'disc' => $candidateTest->discResults->map(function ($result) {
                            return [
                                'id' => $result->id,
                                'dominant_type' => $result->dominant_type,
                                'most_d' => $result->most_d,
                                'most_i' => $result->most_i,
                                'most_s' => $result->most_s,
                                'most_c' => $result->most_c,
                                'least_d' => $result->least_d,
                                'least_i' => $result->least_i,
                                'least_s' => $result->least_s,
                                'least_c' => $result->least_c,
                                'interpretation' => $result->interpretation
                            ];
                        }),
                        'caas' => $candidateTest->caasResults->map(function ($result) {
                            return [
                                'id' => $result->id,
                                'total' => $result->total,
                                'concern' => $result->concern,
                                'control' => $result->control,
                                'curiosity' => $result->curiosity,
                                'confidence' => $result->confidence,
                                'category' => $result->category
                            ];
                        }),
                        'teliti' => $candidateTest->telitiResults->map(function ($result) {
                            return [
                                'id' => $result->id,
                                'score' => $result->score,
                                'total_questions' => $result->total_questions,
                                'category' => $result->category
                            ];
                        })
                    ]
                ];
            });

            // Log activity
            LogActivityService::addToLog("Viewed test results page", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test results retrieved successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve test results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed results for a specific candidate test
     */
    public function show(Request $request, $id)
    {
        try {
            $candidateTest = CandidateTest::with([
                'candidate',
                'test',
                'discResults',
                'caasResults',
                'telitiResults'
            ])->findOrFail($id);

            if ($candidateTest->status !== CandidateTest::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test is not completed yet'
                ], 400);
            }

            $result = [
                'candidate_test' => $candidateTest,
                'candidate' => $candidateTest->candidate,
                'test' => $candidateTest->test,
                'results' => [
                    'disc' => $candidateTest->discResults,
                    'caas' => $candidateTest->caasResults,
                    'teliti' => $candidateTest->telitiResults
                ]
            ];

            // Log activity
            LogActivityService::addToLog("Viewed detailed test results for candidate test ID: {$id}", $request);

            return response()->json([
                'success' => true,
                'message' => 'Test result details retrieved successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve test result details: ' . $e->getMessage()
            ], 500);
        }
    }
}

