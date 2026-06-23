<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('########'),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->unique()->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'delivery_address' => $this->faker->address(),
            'total_price' => $this->faker->numberBetween(50000, 500000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered']),
            'notes' => $this->faker->optional()->sentence(),
            'delivered_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
