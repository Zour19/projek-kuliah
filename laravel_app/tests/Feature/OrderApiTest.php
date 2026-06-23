<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order()
    {
        $product = Product::factory()->create(['price' => 250000]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+62812345678',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.customer_name', 'John Doe');
        $response->assertJsonPath('data.total_price', '500000.00');
        $this->assertDatabaseHas('orders', [
            'customer_email' => 'john@example.com'
        ]);
    }

    public function test_cannot_create_order_with_invalid_email()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'invalid-email',
            'customer_phone' => '+62812345678',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.customer_email', ['Format email tidak valid']);
    }

    public function test_cannot_create_order_with_nonexistent_product()
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+62812345678',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'items' => [
                ['product_id' => 99999, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.items.0.product_id', ['Produk tidak ditemukan']);
    }

    public function test_can_get_order_by_number()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/v1/orders/{$order->order_number}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_can_track_order_by_email()
    {
        $order = Order::factory()->create([
            'customer_email' => 'john@example.com',
            'order_number' => 'ORD-20240115-TEST'
        ]);

        $response = $this->postJson('/api/v1/orders/check', [
            'email' => 'john@example.com',
            'order_number' => 'ORD-20240115-TEST'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.order_number', 'ORD-20240115-TEST');
    }

    public function test_returns_404_when_tracking_nonexistent_order()
    {
        $response = $this->postJson('/api/v1/orders/check', [
            'email' => 'notfound@example.com',
            'order_number' => 'NONEXISTENT'
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Order tidak ditemukan');
    }

    public function test_order_number_format()
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+62812345678',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ]);

        $orderNumber = $response->json('data.order_number');
        $this->assertStringStartsWith('ORD-', $orderNumber);
        $this->assertStringContainsString(date('Ymd'), $orderNumber);
    }

    public function test_order_includes_items()
    {
        $product1 = Product::factory()->create(['price' => 100000]);
        $product2 = Product::factory()->create(['price' => 200000]);

        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+62812345678',
            'delivery_address' => 'Jl. Merdeka No. 1',
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(201);
        $this->assertEquals(2, count($response->json('data.items')));
        $this->assertEquals('500000.00', $response->json('data.total_price'));
    }
}
