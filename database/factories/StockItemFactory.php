<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The stock and item are supplied by the caller.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $previous = fake()->numberBetween(0, 100);
        $current = fake()->numberBetween(0, 100);

        return [
            'previous_balance' => $previous,
            'current_balance' => $current,
            'variance' => $current - $previous,
        ];
    }
}
