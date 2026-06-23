<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('activeProducts');

        return response()->json([
            'data' => $category,
        ]);
    }
}
