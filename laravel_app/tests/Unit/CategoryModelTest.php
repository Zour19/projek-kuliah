<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_products()
    {
        $category = Category::factory()->create();
        Product::factory(3)->create(['category_id' => $category->id]);

        $this->assertEquals(3, $category->products->count());
    }

    public function test_active_scope()
    {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $active = Category::active()->get();

        $this->assertEquals(1, $active->count());
        $this->assertTrue($active->first()->is_active);
    }

    public function test_category_use_slug_as_route_key()
    {
        $category = Category::factory()->create(['slug' => 'test-category']);

        $found = Category::resolveRouteBinding('test-category');

        $this->assertEquals($category->id, $found->id);
    }

    public function test_active_products_relation()
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $category->id, 'status' => 'inactive']);

        $activeProducts = $category->activeProducts;

        $this->assertEquals(1, $activeProducts->count());
        $this->assertEquals('active', $activeProducts->first()->status);
    }
}
