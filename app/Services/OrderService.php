<?php

namespace App\Services;

use App\Repositories\IIngredientRepository;
use App\Repositories\IOrderRepository;

class OrderService implements IOrderService
{
    protected $orderRepository;
    protected $ingredientService;

    public function __construct(IOrderRepository $orderRepository, IngredientService $ingredientService)
    {
        $this->orderRepository = $orderRepository;
        $this->ingredientService = $ingredientService;
    }

    public function create(array $data)
    {
        $this->ingredientService->checkAndUpdate($data['products']);

        return $this->orderRepository->create($data['products']);
    }
}
