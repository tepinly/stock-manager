<?php

namespace Tests\Feature;

use App\Services\OrderService;
use App\Services\IngredientService;
use App\Repositories\OrderRepository;
use App\Models\Product;
use App\Models\Order;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $ingredientService;
    protected $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ingredientService = $this->app->make(IngredientService::class);
        $this->orderRepository = $this->app->make(OrderRepository::class);
        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->ingredientService
        );
    }

    public function testCreateOrder()
    {
        $product1 = Product::factory()->create(['id' => 1, 'name' => 'product 1']);
        $product2 = Product::factory()->create(['id' => 2, 'name' => 'product 2']);

        $data = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 5],
                ['product_id' => $product2->id, 'quantity' => 3],
            ]
        ];

        $result = $this->orderService->create($data);

        $this->assertInstanceOf(Order::class, $result);

        $this->assertDatabaseHas('order_product', [
            'product_id' => $product1->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('order_product', [
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }

    public function testCannotCreateEmptyOrder()
    {
        $data = [
            'products' => []
        ];

        $this->expectException(\Exception::class);

        $this->orderService->create($data);
    }

    public function testCannotCreateOrderWithInvalidProductIds()
    {
        $data = [
            'products' => [
                ['product_id' => 999, 'quantity' => 5],
            ]
        ];

        $this->expectException(\Exception::class);

        $this->orderService->create($data);
    }
}
