<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        $categories = $this->categories();
        $featuredProducts = $this->featuredProducts();

        return view('home', compact('categories', 'featuredProducts'));
    }

    public function category(string $slug)
    {
        $categories = $this->categories();
        $category = collect($categories)->firstWhere('slug', $slug);

        if (! $category) {
            abort(404, 'Category not found');
        }

        $products = collect($this->products())->where('category', $slug)->values()->all();

        return view('category', compact('category', 'products'));
    }

    private function categories(): array
    {
        $file = base_path('../data/categories.json');
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return array_map(fn($cat) => [
                'slug' => $cat['slug'],
                'name' => $cat['name'],
                'description' => $cat['description'],
                'image' => $cat['image'],
            ], $data ?? []);
        }
        
        return [];
    }

    private function featuredProducts(): array
    {
        $products = $this->products();
        $featured = array_filter($products, fn($p) => isset($p['is_featured']) && $p['is_featured']);
        return array_slice(array_values($featured), 0, 6);
    }

    private function products(): array
    {
        $file = base_path('../data/products.json');
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            
            // Get categories to map IDs to slugs
            $categoryFile = base_path('../data/categories.json');
            $catSlugs = [];
            if (file_exists($categoryFile)) {
                $cats = json_decode(file_get_contents($categoryFile), true);
                foreach ($cats as $cat) {
                    $catSlugs[$cat['id']] = $cat['slug'];
                }
            }
            
            return array_map(function($prod) use ($catSlugs) {
                return [
                    'slug' => $prod['slug'],
                    'name' => $prod['name'],
                    'category' => $catSlugs[$prod['category_id']] ?? '',
                    'price' => $prod['price'],
                    'image' => $prod['image'],
                    'description' => $prod['description'] ?? '',
                    'is_featured' => $prod['is_featured'] ?? false,
                ];
            }, $data ?? []);
        }
        
        return [];
    }
}
