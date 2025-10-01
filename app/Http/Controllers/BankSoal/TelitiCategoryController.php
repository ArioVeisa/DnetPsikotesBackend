<?php

namespace App\Http\Controllers\BankSoal;

use App\Http\Controllers\Controller;
use App\Models\telitiCategory;
use Illuminate\Http\Request;

class telitiCategoryController extends Controller
{
    public function index()
    {
        $categories = telitiCategory::all();
        return response()->json([
            'data' => $categories,
            'status' => 'success',
            'message' => 'Categories retrieved successfully'
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:teliti_categories',
        ]);
        $category = telitiCategory::create([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = telitiCategory::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|unique:teliti_categories',
        ]);
        $category->update([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category updated successfully'
        ], 200);
    }

    public function destroy($id)
    {
        $category = telitiCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ], 200);
    }

    public function show($id)
    {
        $category = telitiCategory::findOrFail($id);
        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category retrieved successfully'
        ], 200);
    }
}
