<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $categories = [
            "Écriture et Correction",
            "Papeterie et Feuilles",
            "Géométrie et Calcul",
            "Ardoise et Accessoires",
            "Arts Plastiques",
            "Découpe et Collage",
            "Rangement et Organisation",
            "Fournitures de Bureau",
            "Divers"
        ];
        foreach ($categories as $category) {
            Category::create([
                "name" => $category,
                "description" => $faker->sentence(10),
            ]);
        }
    }
}
