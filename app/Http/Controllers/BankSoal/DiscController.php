<?php

namespace App\Http\Controllers\BankSoal;

use App\Http\Controllers\Controller;
use App\Imports\DiscQuestionImport;
use App\Models\DiscQuestion;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DiscController extends Controller
{
    public function index()
    {
        $questions = DiscQuestion::with('options')->get();
        return response()->json([
            'data' => $questions,
            'status' => 'success',
            'message' => 'Data retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:disc_categories,id',
            'is_active' => 'boolean',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.dimension_most' => 'required|in:D,I,S,C,*',
            'options.*.dimension_least' => 'required|in:D,I,S,C,*',
        ]);

        $question = DiscQuestion::create([
            'question_text' => $validated['question_text'],
            'category_id' => $validated['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);


        foreach ($validated['options'] as $opt) {
            $question->options()->create([
                'option_text' => $opt['option_text'],
                'dimension_most' => $opt['dimension_most'],
                'dimension_least' => $opt['dimension_least'],
            ]);
        }

        // Log activity: HRD creating DISC question
        LogActivityService::addToLog("Created DISC question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question created successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $question = DiscQuestion::findOrFail($id);
        $validated = $request->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:disc_categories,id',
            'is_active' => 'boolean',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.dimension_most' => 'required|in:D,I,S,C,*',
            'options.*.dimension_least' => 'required|in:D,I,S,C,*',

        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'category_id' => $validated['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->has('options')) {
            $question->options()->delete();
            foreach ($request->options as $opt) {
                $question->options()->create([
                    'option_text' => $opt['option_text'],
                    'dimension_most' => $opt['dimension_most'],
                    'dimension_least' => $opt['dimension_least'],
                ]);
            }
        }

        // Log activity: HRD updating DISC question
        LogActivityService::addToLog("Updated DISC question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question updated successfully'
        ]);
    }

    public function show($id)
    {
        $question = DiscQuestion::with('options')->findOrFail($id);
        return response()->json([
            'data' => $question,
            'status' => 'success',
            'message' => 'Question retrieved successfully'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $question = DiscQuestion::findOrFail($id);
        $questionText = $question->question_text;
        $question->options()->delete();
        $question->delete();

        // Log activity: HRD deleting DISC question
        LogActivityService::addToLog("Deleted DISC question: {$questionText}", $request);

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

        Excel::import(new DiscQuestionImport, $request->file('file'));

        // Log activity: HRD importing DISC questions
        LogActivityService::addToLog("Imported DISC questions from file: {$request->file('file')->getClientOriginalName()}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Question Imported successfully'
        ], 200);
    }
}
