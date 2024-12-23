<?php

namespace App\Repositories;

use App\Models\Ingredient;

class IngredientRepository implements IIngredientRepository
{
    public function findManyByProductIds(array $productIds)
    {
        return Ingredient::whereIn('id', function ($query) use ($productIds) {
            $query->select('ingredient_id')
                ->from('ingredient_product')
                ->whereIn('product_id', $productIds);
        })->get();
    }

    public function updateMany(iterable $data)
    {
        foreach ($data as $record) {
            $record->save();
        }
    }
}
