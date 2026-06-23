<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_featured_scope()
    {
        Product::factory()->create(['is_featured' => false]);
        Product::factory()->create(['is_featured' => true]);
        Product::factory()->create(['is_featured' => true]);

        $featured = Product::featured()->get();

        $this->assertEquals(2, $featured->count());
        $this->assertTrue($featured->every(fn($p) => $p->is_featured));
    }

    public function test_active_scope()
    {
        Product::factory()->create(['status' => 'active']);
        Product::factory()->create(['status' => 'inactive']);

        $active = Product::active()->get();

        $this->assertEquals(1, $active->count());
        $this->assertEquals('active', $active->first()->status);
    }

    public function test_in_stock_scope()
    {
        Product::factory()->create(['stock' => 0]);
        Product::factory()->create(['stock' => 10]);

        $inStock = Product::inStock()->get();

        $this->assertEquals(1, $inStock->count());
        $this->assertGreaterThan(0, $inStock->first()->stock);
    }

    public function test_increment_view_count()
    {
        $product = Product::factory()->create(['view_count' => 5]);

        $product->incrementViewCount();

        $this->assertEquals(6, $product->refresh()->view_count);
    }

    public function test_product_use_slug_as_route_key()
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $found = Product::resolveRouteBinding('test-product');

        $this->assertEquals($product->id, $found->id);
    }
}
