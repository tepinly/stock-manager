<?php

namespace App\Services;

use App\Repositories\IIngredientProductRepository;
use App\Repositories\IIngredientRepository;
use App\Mail\IngredientBelowThreshold;
use Illuminate\Support\Facades\Mail;

use Exception;

class IngredientService implements IIngredientService
{
    protected IIngredientRepository $ingredientRepository;
    protected IIngredientProductRepository $ingredientProductRepository;
    protected $threshold_constant;

    public function __construct(IIngredientRepository $ingredientRepository, IIngredientProductRepository $ingredientProductRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
        $this->ingredientProductRepository = $ingredientProductRepository;
        $this->threshold_constant = config('constants.INGREDIENT_STOCK_THRESHOLD');
    }

    public function checkAndUpdate(array $products)
    {
        $ingredientsSum = [];
        $productIds = [];

        foreach ($products as $product) {
            array_push($productIds, $product['product_id']);
        }

        $ingredients = $this->ingredientRepository->findManyByProductIds($productIds);

        foreach ($products as $product) {
            $productIngredients = $this->ingredientProductRepository->findManyByProductId($product['product_id']);

            foreach ($productIngredients as $ingredient) {
                if (!isset($ingredientsSum[$ingredient->ingredient_id])) {
                    $ingredientsSum[$ingredient->ingredient_id] = 0;
                }

                $ingredientsSum[$ingredient->ingredient_id] += $ingredient->weight * $product['quantity'];
            }
        }

        foreach ($ingredients as $ingredient) {
            $ingredient->stock -= $ingredientsSum[$ingredient->id];
            if ($ingredient->stock < 0) {
                throw new Exception('Insufficient ingredient stock to complete the order');
            }
        }

        foreach ($ingredients as $ingredient) {
            $this->checkThreshold($ingredient);
        }

        $this->ingredientRepository->updateMany($ingredients);
    }

    public function checkThreshold($ingredient)
    {
        if (!$ingredient->below_threshold && $ingredient->stock < $ingredient->max_stock * $this->threshold_constant) {
            $ingredient->below_threshold = true;
            $notificationEmail = env('INGREDIENT_NOTIFICATION_EMAIL');
            Mail::to($notificationEmail)->send(new IngredientBelowThreshold($ingredient));
        }
    }
}
