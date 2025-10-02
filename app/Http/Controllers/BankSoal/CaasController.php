<?php

namespace App\Http\Controllers\BankSoal;

use App\Http\Controllers\Controller;
use App\Imports\CaasQuestionImport;
use App\Models\CaasOption;
use App\Models\CaasQuestion;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CaasController extends Controller
{
    public function index()
    {
        $question = CaasQuestion::with('options')->get();
        return response()->json([
            'data' => $question,
            'status' => 'success',
            'message' => 'Questions retrieved successfully'
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:caas_categories,id',
            'is_active' => 'boolean',
            'options' => 'array',
        ]);

        $question = CaasQuestion::create([
            'question_text' => $validated['question_text'],
            'category_id' => $validated['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $defaultOptions = [
            ['option_text' => 'Paling kuat', 'score' => 5],
            ['option_text' => 'Sangat kuat', 'score' => 4],
            ['option_text' => 'Kuat', 'score' => 3],
            ['option_text' => 'Cukup kuat', 'score' => 2],
            ['option_text' => 'Tidak kuat', 'score' => 1],
        ];

        foreach ($defaultOptions as $opt) {
            $question->options()->create($opt);
        }

        // Log activity: HRD creating CAAS question
        LogActivityService::addToLog("Created CAAS question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $question = CaasQuestion::findOrFail($id);
        $validated = $request->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:caas_categories,id',
            'is_active' => 'boolean',
            'options' => 'array',
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'category_id' => $validated['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $question->options()->delete();

        $defaultOptions = [
            ['option_text' => 'Paling kuat', 'score' => 5],
            ['option_text' => 'Sangat kuat', 'score' => 4],
            ['option_text' => 'Kuat', 'score' => 3],
            ['option_text' => 'Cukup kuat', 'score' => 2],
            ['option_text' => 'Tidak kuat', 'score' => 1],
        ];

        foreach ($defaultOptions as $opt) {
            $question->options()->create($opt);
        }

        // Log activity: HRD updating CAAS question
        LogActivityService::addToLog("Updated CAAS question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question updated successfully'
        ], 200);
    }

    public function show($id)
    {
        $question = CaasQuestion::with('options')->findOrFail($id);
        return response()->json([
            'data' => $question,
            'status' => 'success',
            'message' => 'Question retrieved successfully'
        ], 200);
    }


    public function destroy(Request $request, $id)
    {
        $question = CaasQuestion::findOrFail($id);
        $questionText = $question->question_text;
        $question->options()->delete();
        $question->delete();

        // Log activity: HRD deleting CAAS question
        LogActivityService::addToLog("Deleted CAAS question: {$questionText}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Question deleted successfully'
        ], 200);
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new CaasQuestionImport, $request->file('file'));

        // Log activity: HRD importing CAAS questions
        LogActivityService::addToLog("Imported CAAS questions from file: {$request->file('file')->getClientOriginalName()}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Question Imported successfully'
        ], 200);
    }
}
