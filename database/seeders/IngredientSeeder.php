<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        define('BEEF_STOCK', 20000);
        define('CHEESE_STOCK', 5000);
        define('ONION_STOCK', 1000);

        $ingredients = [
            [
                'name' => 'beef',
                'stock' => BEEF_STOCK,
                'max_stock' => BEEF_STOCK,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cheese',
                'stock' => CHEESE_STOCK,
                'max_stock' => CHEESE_STOCK,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'onion',
                'stock' => ONION_STOCK,
                'max_stock' => ONION_STOCK,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Ingredient::insert($ingredients);
    }
}
