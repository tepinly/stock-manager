<?php

namespace App\Repositories;

interface IIngredientRepository
{
    public function findManyByProductIds(array $productIds);
    public function updateMany(iterable $data);
}
