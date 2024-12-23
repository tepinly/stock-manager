<?php

namespace App\Services;

use App\Repositories\IIngredientProductRepository;
use App\Repositories\IIngredientRepository;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IngredientService implements IIngredientService
{
    protected IIngredientRepository $ingredientRepository;
    protected IIngredientProductRepository $ingredientProductRepository;

    public function __construct(IIngredientRepository $ingredientRepository, IIngredientProductRepository $ingredientProductRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
        $this->ingredientProductRepository = $ingredientProductRepository;
    }

    public function checkAndUpdate(array $products)
    {
        $ingredientsSum = [];
        $productIds = [];
        $threshold = config('constants.INGREDIENT_STOCK_THRESHOLD');

        foreach ($products as $product) {
            array_push($productIds, $product['product_id']);
        }

        $ingredients = $this->ingredientRepository->findManyByProductIds($productIds);

        foreach ($products as $product) {
            $productIngredients = $this->ingredientProductRepository->findManyByProductId([$product['product_id']]);

            foreach ($productIngredients as $ingredient) {
                if (!isset($ingredientsSum[$ingredient->id])) {
                    $ingredientsSum[$ingredient->id] = 0;
                }

                $ingredientsSum[$ingredient->id] += $ingredient['weight'] * $product['quantity'];
            }
        }

        foreach ($ingredients as $ingredient) {
            $ingredient->stock -= $ingredientsSum[$ingredient->id];
            if ($ingredient->stock < 0) {
                throw new BadRequestHttpException('Insufficient stock to complete the order');
            }

            if (!$ingredient->below_threshold && $ingredient->stock <= $ingredient->max_stock * $threshold) {
                $ingredient->below_threshold = true;
                //TODO: Implement email notification
            }
        }

        $this->ingredientRepository->updateMany($ingredients);
    }
}
