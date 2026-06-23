<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(50000, 500000),
            'image' => $this->faker->imageUrl(640, 480),
            'is_featured' => $this->faker->boolean(30),
            'status' => 'active',
        ];
    }
}
