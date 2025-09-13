<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Produit;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockHistory>
 */
class StockHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produit_id' => Produit::inRandomOrder()->first()?->id ?? Produit::factory(),
            'type_mouvement' => $this->faker->randomElement(['entree', 'sortie', 'ajustement']),
            'quantite' => $this->faker->numberBetween(1, 20),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'commentaire' => $this->faker->sentence(),
        ];
    }
}
