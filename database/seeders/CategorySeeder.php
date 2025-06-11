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
            'stylo',
            'Crayon à papier',
            'Porte-mine',
            'Gomme',
            'Taille-crayon',
            'Correcteur',
            'Marqueur',
            'Stabilo (surligneur)',
            'Feutres de couleurs',
            'Cahiers ',
            'Bloc-notes',
            'Fiches bristol',
            'Feuilles simples',
            'Copies doubles',
            'Papier millimétré',
            'Carnet de brouillon',
            'Carnet de vocabulaire',
            'Règle',
            'Équerre',
            'Rapporteur',
            'Compas',
            'Calculatrice simple',
            'Calculatrice scientifique',
            'Ardoise ',
            'feutre ',
            'Crayons de couleur',
            'Pastels',
            'Peinture',
            'pinceaux',
            'Gobelet pour eau',
            'Tabliers',
            'Ciseaux',
            'Colle',
            'Papier de couleur',
            'Papier Canson',
            'Trousse',
            'Cartable / Sac à dos',
            'Pochette plastique perforée',
            'Classeur',
            'Intercalaires',
            'Chemise à rabats',
            'Porte-documents',
            'Agrafeuse',
            'Scotch / ruban adhésif',
            'Attaches parisiennes',
            'Mouchoirs',
            'Bouteille d’eau'
        ];
        foreach ($categories as $category) {
            Category::create([
                "name" => $category,
                "description" => $faker->sentence(10),
            ]);
        }
    }
}
