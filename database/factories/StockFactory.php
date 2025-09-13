<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Produit;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
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
            'quantite_actuelle' => $this->faker->numberBetween(5, 100),
            'seuil_minimum' => 5,
            'statut' => 'in_stock',
        ];
    }
}
