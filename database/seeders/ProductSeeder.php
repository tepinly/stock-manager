<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Ingredient;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $burger = Product::create(['name' => 'burger']);

        $burger->ingredients()->attach([
            Ingredient::where('name', 'beef')->first()->id => [
                'weight' => 150,
            ],
            Ingredient::where('name', 'cheese')->first()->id => [
                'weight' => 30,
            ],
            Ingredient::where('name', 'onion')->first()->id => [
                'weight' => 20,
            ],
        ]);
    }
}
