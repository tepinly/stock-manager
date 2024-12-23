<?php

namespace App\Repositories;

interface IIngredientProductRepository
{
    public function findManyByProductId(array $productId);
}
