<?php

namespace App\Http\Controllers\BankSoal;

use App\Http\Controllers\Controller;
use App\Imports\TelitiQuestionImport;
use App\Models\TelitiQuestion;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TelitiController extends Controller
{
    public function index()
    {
        $question = TelitiQuestion::with('options')->get();
        return response()->json([
            'data' => $question,
            'status' => 'success',
            'message' => 'Questions retrieved successfully'
        ], 200);
    }

    public function store(Request $request)
    {

        $options = collect($request->input('options', []))->map(function ($option) {
            $option['is_correct'] = filter_var($option['is_correct'], FILTER_VALIDATE_BOOLEAN);
            return $option;
        })->toArray();

        $validatedData = $request->merge(['options' => $options])->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:teliti_categories,id',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question = TelitiQuestion::create([
            'question_text' => $validatedData['question_text'],
            'category_id' => $validatedData['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $request->input('is_active', true),
            'correct_option_id' => null,
        ]);

        $correctOptionId = null;

        foreach ($validatedData['options'] as $optionData) {
            $option = $question->options()->create([
                'option_text' => $optionData['option_text'],
            ]);

            // Ambil ID dari opsi yang benar
            if ($optionData['is_correct']) {
                $correctOptionId = $option->id;
            }
        }

        // Update pertanyaan dengan opsi yang benar
        if ($correctOptionId) {
            $question->update(['correct_option_id' => $correctOptionId]);
        }
        // Log activity: HRD creating teliti question
        LogActivityService::addToLog("Created teliti question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $options = collect($request->input('options', []))->map(function ($option) {
            $option['is_correct'] = filter_var($option['is_correct'], FILTER_VALIDATE_BOOLEAN);
            return $option;
        })->toArray();

        $validatedData = $request->merge(['options' => $options])->validate([
            'question_text' => 'required',
            'category_id' => 'required|exists:teliti_categories,id',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question = TelitiQuestion::findOrFail($id);
        $question->update([
            'question_text' => $validatedData['question_text'],
            'category_id' => $validatedData['category_id'],
            'media_path' => $request->file('media') ? $request->file('media')->store('media', 'public') : null,
            'is_active' => $request->input('is_active', true),
        ]);

        $question->options()->delete();

        $correctOptionId = null;

        foreach ($validatedData['options'] as $optionData) {
            $option = $question->options()->create([
                'option_text' => $optionData['option_text'],
            ]);

            // Ambil ID dari opsi yang benar
            if ($optionData['is_correct']) {
                $correctOptionId = $option->id;
            }
        }

        // Update pertanyaan dengan opsi yang benar
        if ($correctOptionId) {
            $question->update(['correct_option_id' => $correctOptionId]);
        }
        // Log activity: HRD updating teliti question
        LogActivityService::addToLog("Updated teliti question: {$question->question_text}", $request);

        return response()->json([
            'data' => $question->load('options'),
            'status' => 'success',
            'message' => 'Question updated successfully'
        ], 200);
    }

    public function show($id)
    {
        $question = TelitiQuestion::with('options')->findOrFail($id);
        return response()->json([
            'data' => $question,
            'status' => 'success',
            'message' => 'Question retrieved successfully'
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $question = TelitiQuestion::findOrFail($id);
        $questionText = $question->question_text;
        $question->options()->delete();
        $question->delete();

        // Log activity: HRD deleting teliti question
        LogActivityService::addToLog("Deleted teliti question: {$questionText}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Question deleted successfully'
        ], 200);
    }

    public function import(Request $request)
    {

        // if (!$request->hasFile('file')) {
        //     return response()->json(['error' => 'file not received'], 400);
        // }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new TelitiQuestionImport, $request->file('file'));

        // Log activity: HRD importing teliti questions
        LogActivityService::addToLog("Imported teliti questions from file: {$request->file('file')->getClientOriginalName()}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Question Imported successfully'
        ], 200);
    }
}
