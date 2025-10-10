<?php

namespace App\Http\Controllers\ManajemenTes;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class TestController extends Controller
{

    public function index()
    {
        $tests = Test::with('sections')->get();
        return response()->json([
            'data' => $tests,
            'status' => 'success',
            'message' => 'Tests retrieved successfully'
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'target_position' => 'nullable|string',
            'icon_path' => 'nullable|string',
            'started_date' => 'nullable|date',
            'sections' => 'required|array|min:1',
            'sections.*.section_type' => 'required|in:DISC,CAAS,teliti',
            'sections.*.duration_minutes' => 'required|integer',
            'sections.*.question_count' => 'nullable|integer',
            'sections.*.sequence' => 'required|integer',
        ]);

        $test = Test::create($validated);

        foreach ($validated['sections'] as $section) {
            $test->sections()->create($section);
        }

        // Log activity: HRD creating test package
        LogActivityService::addToLog("Created test package: {$test->name} (Target: {$test->target_position})", $request);

        return response()->json([
            'data' => $test->load('sections'),
            'status' => 'success',
            'message' => 'Test created with sections successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $test = Test::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'target_position' => 'nullable|string',
            'icon_path' => 'nullable|string',
            'started_date' => 'nullable|date',
            'sections' => 'required|array|min:1',
            'sections.*.section_type' => 'required|in:DISC,CAAS,teliti',
            'sections.*.duration_minutes' => 'required|integer',
            'sections.*.question_count' => 'nullable|integer',
            'sections.*.sequence' => 'required|integer',
        ]);

        $test->update([
            'name' => $validated['name'],
            'target_position' => $validated['target_position'],
            'icon_path' => $validated['icon_path'] ?? null,
            'started_date' => $validated['started_date'] ?? null,
        ]);

        if ($request->has('sections')) {
            $test->sections()->delete();
            foreach ($validated['sections'] as $section) {
                $test->sections()->create($section);
            }
        }
        // Log activity: HRD updating test package
        LogActivityService::addToLog("Updated test package: {$test->name} (Target: {$test->target_position})", $request);

        return response()->json([
            'data' => $test->load('sections'),
            'status' => 'success',
            'message' => 'Test updated successfully'
        ]);
    }
    public function show($id)
    {
        $test = Test::with('sections')->findOrFail($id);
        return response()->json([
            'data' => $test,
            'status' => 'success',
            'message' => 'Test retrieved successfully'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $test = Test::findOrFail($id);
        $testName = $test->name;
        $test->delete();

        // Log activity: HRD deleting test package
        LogActivityService::addToLog("Deleted test package: {$testName}", $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Test deleted successfully'
        ]);
    }

    public function duplicate(Request $request, $id)
    {
        $existingTest = Test::with('sections.testQuestions')->findOrFail($id);

        $newTest = Test::create([
            'name' => $existingTest->name . ' (Copy)',
            'target_position' => $existingTest->target_position,
            'icon_path' => $existingTest->icon_path,
            'started_date' => now(),
        ]);

        foreach ($existingTest->sections as $section) {
            $newSection = $newTest->sections()->create([
                'section_type' => $section->section_type,
                'duration_minutes' => $section->duration_minutes,
                'question_count' => $section->question_count,
                'sequence' => $section->sequence,
            ]);

            foreach ($section->testQuestions as $testQuestion) {
                $newSection->testQuestions()->create([
                    'test_id' => $newTest->id,
                    'question_id' => $testQuestion->question_id,
                    'question_type' => $testQuestion->question_type,
                    'section_id' => $newSection->id,
                ]);
            }
        }

        // Log activity: HRD duplicating test package
        LogActivityService::addToLog("Duplicated test package: {$existingTest->name} -> {$newTest->name}", $request);

        return response()->json([
            'data' => $newTest->load('sections'),
            'status' => 'success',
            'message' => 'Test duplicated successfully'
        ]);
    }
}
