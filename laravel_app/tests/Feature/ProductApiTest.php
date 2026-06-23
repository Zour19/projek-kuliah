<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::factory(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'image']
            ]
        ]);
    }

    public function test_can_filter_products_by_category()
    {
        $category = Category::factory()->create();
        Product::factory(2)->create(['category_id' => $category->id]);
        Product::factory(1)->create();

        $response = $this->getJson("/api/v1/products?category={$category->slug}");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.0.category_id'));
    }

    public function test_can_filter_featured_products()
    {
        Product::factory()->create(['is_featured' => true]);
        Product::factory()->create(['is_featured' => false]);

        $response = $this->getJson('/api/v1/products?featured=true');

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.0.is_featured'));
    }

    public function test_can_search_products()
    {
        Product::factory()->create(['name' => 'Romantic Rose Bouquet']);
        Product::factory()->create(['name' => 'Sunflower Bundle']);

        $response = $this->getJson('/api/v1/products?search=rose');

        $response->assertStatus(200);
        $this->assertStringContainsString('rose', strtolower($response->json('data.0.name')));
    }

    public function test_can_get_featured_products()
    {
        Product::factory(3)->create(['is_featured' => true]);
        Product::factory(2)->create(['is_featured' => false]);

        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(6, count($response->json('data')));
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $product->id);
        $response->assertJsonPath('data.name', $product->name);
    }

    public function test_view_count_increments_on_product_view()
    {
        $product = Product::factory()->create(['view_count' => 0]);

        $this->getJson("/api/v1/products/{$product->id}");
        $this->getJson("/api/v1/products/{$product->id}");

        $this->assertEquals(2, $product->refresh()->view_count);
    }

    public function test_returns_404_for_nonexistent_product()
    {
        $response = $this->getJson('/api/v1/products/99999');

        $response->assertStatus(404);
    }
}
