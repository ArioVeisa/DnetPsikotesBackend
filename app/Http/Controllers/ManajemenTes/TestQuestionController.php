<?php

namespace App\Http\Controllers\ManajemenTes;

use App\Http\Controllers\Controller;
use App\Models\CaasQuestion;
use App\Models\DiscQuestion;
use App\Models\TelitiQuestion;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Http\Request;

class TestQuestionController extends Controller
{
    public function index()
    {
        $testQuestions = TestQuestion::with(['test', 'section'])->get();
        return response()->json([
            'data' => $testQuestions,
            'status' => 'success',
            'message' => 'Test questions retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.test_id' => 'required|exists:tests,id',
            'questions.*.question_id' => 'required|integer',
            'questions.*.question_type' => 'required|in:CAAS,DISC,teliti',
            'questions.*.section_id' => 'nullable|exists:test_sections,id',
            'questions.*.sequence' => 'nullable|integer'
        ]);

        $savedQuestions = [];
        foreach ($validated['questions'] as $questionData) {
            $exists = match ($questionData['question_type']) {
                'CAAS' => CaasQuestion::where('id', $questionData['question_id'])->exists(),
                'DISC' => DiscQuestion::where('id', $questionData['question_id'])->exists(),
                'teliti' => TelitiQuestion::where('id', $questionData['question_id'])->exists(),
            };

            if (!$exists) {
                return response()->json([
                    'message' => "Invalid question_id for {$questionData['question_type']}.",
                    'question' => $questionData['question_id'],
                ], 422);
            }

            $savedQuestions[] = TestQuestion::create($questionData);
        }

        return response()->json([
            'data' => $savedQuestions,
            'status' => 'success',
            'message' => 'Questions added to test successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $testQuestion = TestQuestion::findOrFail($id);

        $validated = $request->validate([
            'test_id' => 'required|exists:tests,id',
            'question_id' => 'required|integer',
            'question_type' => 'required|in:CAAS,DISC,teliti',
            'section_id' => 'nullable|exists:test_sections,id',
            'sequence' => 'nullable|integer'
        ]);

        $exists = match ($validated['question_type']) {
            'CAAS' => CaasQuestion::where('id', $validated['question_id'])->exists(),
            'DISC' => DiscQuestion::where('id', $validated['question_id'])->exists(),
            'teliti' => TelitiQuestion::where('id', $validated['question_id'])->exists(),
        };

        if (!$exists) {
            return response()->json([
                'message' => "Invalid question_id for {$validated['question_type']}."
            ], 422);
        }

        $testQuestion->update($validated);

        return response()->json([
            'data' => $testQuestion,
            'status' => 'success',
            'message' => 'Test question updated successfully'
        ]);
    }

    public function show($id)
    {
        $testQuestion = TestQuestion::with(['test', 'section'])->findOrFail($id);
        return response()->json([
            'data' => $testQuestion,
            'status' => 'success',
            'message' => 'Test question retrieved successfully'
        ]);
    }

    public function destroy($id)
    {
        $testQuestion = TestQuestion::findOrFail($id);
        $testQuestion->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Test question deleted successfully'
        ]);
    }

    public function showSection($sectionId)
    {
        $section = TestSection::with(['testQuestions.test', 'testQuestions.section'])
            ->findOrFail($sectionId);

        $data = [
            'section_id' => $section->id,
            'section_type' => $section->section_type,
            'questions' => $section->testQuestions->map(function ($tq) {
                return [
                    'id' => $tq->id,
                    'question_id' => $tq->question_id,
                    'question_type' => $tq->question_type,
                    'question_detail' => $tq->question_detail ?? null,
                ];
            })
        ];

        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => 'Section with questions retrieved successfully'
        ]);
    }

    public function showTestWithSections($testId)
    {
        $sections = TestSection::with('testQuestions')
            ->where('test_id', $testId)
            ->get();

        $data = [
            'test_id' => $testId,
            'sections' => $sections->map(function ($section) {
                return [
                    'section_id' => $section->id,
                    'section_type' => $section->section_type,
                    'duration_minutes' => $section->duration_minutes,
                    'question_count' => $section->question_count,
                    'questions' => $section->testQuestions->map(function ($tq) {
                        return [
                            'id' => $tq->id,
                            'question_id' => $tq->question_id,
                            'question_type' => $tq->question_type,
                            'question_detail' => $tq->question_detail ?? null,
                        ];
                    })
                ];
            })
        ];

        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => 'Test with sections and questions retrieved successfully'
        ]);
    }
}
