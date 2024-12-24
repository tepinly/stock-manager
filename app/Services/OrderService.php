<?php

namespace App\Services;

use App\Repositories\IOrderRepository;

use Exception;

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
        if (empty($data['products'])) {
            throw new Exception('Cannot create an order with no products.');
        }

        $this->ingredientService->checkAndUpdate($data['products']);

        return $this->orderRepository->create($data['products']);
    }
}
