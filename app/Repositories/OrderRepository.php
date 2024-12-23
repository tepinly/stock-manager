<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository implements IOrderRepository
{
    public function create(array $data)
    {
        $order = Order::create();

        foreach ($data as $record) {
            $order->products()->attach($record['product_id'], ['quantity' => $record['quantity']]);
        }
    }
}
