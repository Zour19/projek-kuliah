<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->numberBetween(50000, 500000);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'product_name' => $this->faker->words(3, true),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $unitPrice * $quantity,
        ];
    }
}
