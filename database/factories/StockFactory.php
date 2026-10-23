<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The business and creator are supplied by the caller, since Business has no factory.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => 'ST-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'stock_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}
