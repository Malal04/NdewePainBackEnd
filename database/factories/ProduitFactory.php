<?php

namespace Database\Factories;

use App\Models\Categorie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'categorie_id' => Categorie::inRandomOrder()->first()?->id ?? Categorie::factory(),
            'nom' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(),
            'description' => $this->faker->paragraph(),
            'prix_unitaire' => $this->faker->randomFloat(2, 100, 10000),
            'photo_url' => null,
            'stock' => $this->faker->numberBetween(5, 50),
            'allergenes' => $this->faker->randomElements(['Contient Gluten','Contient Lait','Contient Oeuf','Contient Arachide', 'Contient Soja'], rand(0, 3)),
            'status' => 'active',
        ];
    }
}
