<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cats = ['Pains', 'Viennoiseries', 'Pâtisseries', 'Boissons', 'Bread','Cake','Croissant','Pastry','Dessert'];

        foreach ($cats as $cat) {
            Categorie::factory()->create([
                'nom' => $cat,
                'slug' => Str::slug($cat),
                'status' => 'active',
            ]);
        }
    }
}
