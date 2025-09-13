<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'Promo ' . $this->faker->word(),
            'description' => $this->faker->sentence(),
            'type_remise' => $this->faker->randomElement(['pourcentage', 'montant', 'bogo', 'gratuit_livraison']),
            'valeur_remise' => $this->faker->randomFloat(2, 5, 50),
            'code_promo' => strtoupper(Str::random(6)),
            'conditions' => $this->faker->sentence(),
            'date_debut' => now(),
            'date_fin' => now()->addDays(10),
            'status' => 'active',
        ];
    }
}
