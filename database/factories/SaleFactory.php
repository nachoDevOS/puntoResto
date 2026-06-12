<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 10, 200);

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['mesa', 'llevar']),
            'table_number' => null,
            'payment_method' => 'efectivo',
            'cash_amount' => $total,
            'qr_amount' => 0,
            'total' => $total,
        ];
    }
}
