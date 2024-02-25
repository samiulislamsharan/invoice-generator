<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => fake()->numberBetween(100, 1000),
            'product_id' => fake()->numberBetween(10, 100),
            'unit_price' => fake()->numberBetween(100, 5000),
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}