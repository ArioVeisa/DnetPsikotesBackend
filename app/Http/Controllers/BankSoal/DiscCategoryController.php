<?php

namespace App\Http\Controllers\BankSoal;

use App\Http\Controllers\Controller;
use App\Models\DiscCategory;
use Illuminate\Http\Request;

class DiscCategoryController extends Controller
{
    public function index()
    {
        $categories = DiscCategory::all();

        return response()->json([
            'data' => $categories,
            'status' => 'success',
            'message' => 'Data retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:disc_categories',
        ]);
        $category = DiscCategory::create([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category created successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = DiscCategory::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|unique:disc_categories,name,' . $id,
        ]);
        $category->update([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category updated successfully'
        ]);
    }

    public function show($id)
    {
        $category = DiscCategory::findOrFail($id);

        return response()->json([
            'data' => $category,
            'status' => 'success',
            'message' => 'Category retrieved successfully'
        ]);
    }

    public function destroy($id)
    {
        $category = DiscCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
