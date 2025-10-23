<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\CaasOption;
use App\Models\CaasQuestion;
use App\Models\CaasResult;
use App\Models\CandidateAnswer;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class CaasResultController extends Controller
{
    public function index()
    {
        $result = CaasResult::all();
        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Results retrieved successfully'
        ]);
    }

    public function show(Request $request, $id){
        $result = CaasResult::where('candidate_test_id', $id)->first();
        if (!$result) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Result not found'
            ], 404);
        }

        // Log activity: HRD viewing test result details
        LogActivityService::addToLog("Viewed CAAS test result details for candidate test ID: {$id}", $request);

        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Result retrieved successfully'
        ]);
    }

    public function calculateByIds($candidateTestId, $sectionId, $answers = null)
    {
        if ($answers === null) {
            $answers = CandidateAnswer::where('candidate_test_id', $candidateTestId)
                ->where('section_id', $sectionId)
                ->get()
                ->map(function ($a) {
                    return [
                        'question_id' => $a->question_id,
                        'option_id'   => $a->selected_option_id,
                    ];
                });
        }

        $scores = [
            'concern' => 0,
            'control' => 0,
            'curiosity' => 0,
            'confidence' => 0,
        ];

        foreach ($answers as $answer) {
            // Skip jika tidak ada option_id (tidak dijawab)
            if (empty($answer['option_id'])) {
                continue;
            }
            
            // Get the test_question record to get the actual question details
            $testQuestion = \App\Models\TestQuestion::find($answer['question_id']);
            
            if ($testQuestion && $testQuestion->question_type === 'caas') {
                // Get the actual caas question using the question_id from test_questions
                $question = CaasQuestion::find($testQuestion->question_id);
                if ($question) {
                    $option = CaasOption::find($answer['option_id']);
                    if ($option) {
                        switch ($question->category_id) {
                            case 1:
                                $scores['concern'] += $option->score;
                                break;
                            case 2:
                                $scores['control'] += $option->score;
                                break;
                            case 3:
                                $scores['curiosity'] += $option->score;
                                break;
                            case 4:
                                $scores['confidence'] += $option->score;
                                break;
                        }
                    }
                }
            }
        }

        $total = array_sum($scores);
        $category = $this->getCategory($total);

        // Simpan ke DB
        CaasResult::updateOrCreate(
            [
                'candidate_test_id' => $candidateTestId,
                'section_id'        => $sectionId,
            ],
            [
                'concern'    => $scores['concern'],
                'control'    => $scores['control'],
                'curiosity'  => $scores['curiosity'],
                'confidence' => $scores['confidence'],
                'total'      => $total,
                'category'   => $category,
            ]
        );

        return [
            'dimension_scores' => $scores,
            'total' => $total,
            'category' => $category,
        ];
    }

    private function getCategory($total)
    {
        if ($total < 56) {
            return 'Rendah';
        } elseif ($total < 88) {
            return 'Sedang';
        } else {
            return 'Tinggi';
        }
    }
}
