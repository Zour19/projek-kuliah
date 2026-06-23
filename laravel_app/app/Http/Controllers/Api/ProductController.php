<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', fn($q) => 
                $q->where('slug', $request->category)
            );
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 12), 100);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->incrementViewCount();

        return response()->json([
            'data' => $product->load('category'),
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }
}
