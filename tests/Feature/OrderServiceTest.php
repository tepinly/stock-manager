<?php

namespace Tests\Feature;

use App\Services\OrderService;
use App\Services\IngredientService;
use App\Repositories\OrderRepository;
use App\Models\Product;
use App\Models\Order;
use App\Mail\IngredientBelowThreshold;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $ingredientService;
    protected $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Mockery\MockInterface|\App\Services\IngredientService */
        $this->ingredientService = Mockery::mock(IngredientService::class);

        /** @var \Mockery\MockInterface|\App\Repositories\OrderRepository */
        $this->orderRepository = Mockery::mock(OrderRepository::class);
        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->ingredientService
        );
    }

    public function testCreateOrder()
    {
        Mail::fake();

        $product1 = Product::factory()->create(['id' => 1, 'name' => 'product 1']);
        $product2 = Product::factory()->create(['id' => 2, 'name' => 'product 2']);

        $data = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 5],
                ['product_id' => $product2->id, 'quantity' => 3],
            ]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 100, 'max_stock' => 200, 'below_threshold' => false]
        ];

        $this->ingredientService
            ->shouldReceive('checkAndUpdate')
            ->with($data['products'])
            ->andReturn([$ingredients[0]]);

        $this->orderRepository
            ->shouldReceive('create')
            ->with($data['products'])
            ->andReturnUsing(function ($products) {
                $order = Order::create();
                foreach ($products as $product) {
                    $order->products()->attach($product['product_id'], ['quantity' => $product['quantity']]);
                }
                return $order;
            });

        $this->ingredientService
            ->shouldReceive('sendEmails')
            ->with(Mockery::on(function ($emailList) use ($ingredients) {
                return $emailList[0]->id === $ingredients[0]->id;
            }));

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

        // Assert that the email list is not empty
        $emailList = $this->ingredientService->checkAndUpdate($data['products']);
        $this->assertNotEmpty($emailList);
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

    public function testEmailsAreSentWhenEmailListIsNotEmpty()
    {
        Mail::fake();

        $product1 = Product::factory()->create(['name' => 'product 1']);
        $product2 = Product::factory()->create(['name' => 'product 2']);

        $data = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 5],
                ['product_id' => $product2->id, 'quantity' => 3],
            ]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 100, 'max_stock' => 200, 'below_threshold' => false]
        ];

        $this->ingredientService
            ->shouldReceive('checkAndUpdate')
            ->with($data['products'])
            ->andReturn([$ingredients[0]]);

        $this->orderRepository
            ->shouldReceive('create')
            ->with($data['products'])
            ->andReturnUsing(function ($products) {
                $order = Order::create();
                foreach ($products as $product) {
                    $order->products()->attach($product['product_id'], ['quantity' => $product['quantity']]);
                }
                return $order;
            });

        $this->ingredientService
            ->shouldReceive('sendEmails')
            ->with(Mockery::on(function ($emailList) use ($ingredients) {
                return $emailList[0]->id === $ingredients[0]->id;
            }))
            ->andReturnUsing(function ($emailList) {
                foreach ($emailList as $ingredient) {
                    Mail::to(config('mail.from.address'))->send(new IngredientBelowThreshold($ingredient));
                }
            });

        $this->orderService->create($data);

        Mail::assertSent(IngredientBelowThreshold::class, function ($mail) use ($ingredients) {
            return $mail->ingredient->id === $ingredients[0]->id;
        });
    }
}
