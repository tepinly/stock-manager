<?php

namespace App\Repositories;

use App\Models\IngredientProduct;
use App\Repositories\IIngredientProductRepository;

class IngredientProductRepository implements IIngredientProductRepository
{
    public function findManyByProductId($productId)
    {
        return IngredientProduct::where('product_id', $productId)->get();
    }
}
