<?php

namespace App\Http\Controllers\Results;

use App\Http\Controllers\Controller;
use App\Models\CandidateAnswer;
use App\Models\TelitiQuestion;
use App\Models\TelitiResult;
use App\Models\TestSection;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class TelitiResultController extends Controller
{
    public function index()
    {
        $result = TelitiResult::all();
        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Results retrieved successfully'
        ]);
    }

    public function show(Request $request, $id)
    {
        $result = TelitiResult::where('candidate_test_id', $id)->first();
        if (!$result) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Result not found'
            ], 404);
        }

        // Log activity: HRD viewing test result details
        LogActivityService::addToLog("Viewed teliti test result details for candidate test ID: {$id}", $request);

        return response()->json([
            'data' => $result,
            'status' => 'success',
            'message' => 'Result retrieved successfully'
        ]);
    }

    public function calculateByIds($candidateTestId, $sectionId, $answers = null)
    {
        if ($answers === null) {
            // ambil jawaban langsung dari DB
            $answers = CandidateAnswer::where('candidate_test_id', $candidateTestId)
                ->where('section_id', $sectionId)
                ->get()
                ->map(function ($a) {
                    return [
                        'question_id' => $a->question_id,
                        'answer_id' => $a->selected_option_id,
                    ];
                });
        }

        $section = TestSection::with('testQuestions')->findOrFail($sectionId);
        $score = 0;
        $totalQuestions = $section->testQuestions()->count();

        foreach ($answers as $answer) {
            // Skip jika tidak ada answer_id (tidak dijawab)
            if (empty($answer['answer_id'])) {
                continue;
            }
            
            // Get the test_question record to get the actual question details
            $testQuestion = \App\Models\TestQuestion::find($answer['question_id']);
            
            if ($testQuestion && $testQuestion->question_type === 'teliti') {
                // Get the actual teliti question using the question_id from test_questions
                $question = TelitiQuestion::find($testQuestion->question_id);
                if ($question && $question->correct_option_id == $answer['answer_id']) {
                    $score += 1;
                }
            }
        }

        $category = $this->getCategory($score);

        TelitiResult::updateOrCreate(
            [
                'candidate_test_id' => $candidateTestId,
                'section_id' => $sectionId,
            ],
            [
                'score' => $score,
                'total_questions' => $totalQuestions,
                'category' => $category,
            ]
        );


        return [
            'score' => $score,
            'total_questions' => $totalQuestions,
            'category' => $category,
        ];
    }

    private function getCategory($score)
    {
        if ($score >= 56 && $score <= 60) {
            return 'SANGAT AKURAT';
        } elseif ($score >= 41 && $score <= 55) {
            return 'AKURAT';
        } elseif ($score >= 21 && $score <= 40) {
            return 'CUKUP AKURAT';
        } elseif ($score >= 6 && $score <= 20) {
            return 'KURANG AKURAT';
        } else {
            return 'SANGAT KURANG AKURAT';
        }
    }
}
