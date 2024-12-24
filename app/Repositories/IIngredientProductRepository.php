<?php

namespace App\Repositories;

interface IIngredientProductRepository
{
    public function findManyByProductId(int $productId);
}
