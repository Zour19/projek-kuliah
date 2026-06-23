<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_order_number()
    {
        $orderNumber = Order::generateOrderNumber();

        $this->assertStringStartsWith('ORD-', $orderNumber);
        $this->assertStringContainsString(date('Ymd'), $orderNumber);
    }

    public function test_order_number_is_unique()
    {
        $number1 = Order::generateOrderNumber();
        $number2 = Order::generateOrderNumber();

        $this->assertNotEquals($number1, $number2);
    }

    public function test_mark_as_confirmed()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->markAsConfirmed();

        $this->assertEquals('confirmed', $order->refresh()->status);
    }

    public function test_mark_as_delivered()
    {
        $order = Order::factory()->create(['status' => 'shipped']);

        $order->markAsDelivered();

        $this->assertEquals('delivered', $order->refresh()->status);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_cancel_order()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->cancel();

        $this->assertEquals('cancelled', $order->refresh()->status);
    }

    public function test_pending_scope()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'confirmed']);

        $pending = Order::pending()->get();

        $this->assertEquals(1, $pending->count());
        $this->assertEquals('pending', $pending->first()->status);
    }
}
