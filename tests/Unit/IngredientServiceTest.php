<?php

namespace Tests\Unit;

use App\Repositories\IIngredientProductRepository;
use App\Repositories\IIngredientRepository;
use App\Services\IngredientService;
use Tests\TestCase;
use Mockery;
use Exception;

class IngredientServiceTest extends TestCase
{
    protected $ingredientService;
    protected $ingredientRepository;
    protected $ingredientProductRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Mockery\MockInterface|\App\Repositories\IIngredientRepository */
        $this->ingredientRepository = Mockery::mock(IIngredientRepository::class);

        /** @var \Mockery\MockInterface|\App\Repositories\IIngredientProductRepository */
        $this->ingredientProductRepository = Mockery::mock(IIngredientProductRepository::class);

        $this->ingredientService = new IngredientService(
            $this->ingredientRepository,
            $this->ingredientProductRepository
        );
    }

    public function testThrowExceptionWhenStockIsInsufficient()
    {
        $this->expectException(Exception::class);

        $products = [
            ['product_id' => 1, 'quantity' => 10]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 5, 'max_stock' => 100, 'below_threshold' => false]
        ];

        $productIngredients = [
            (object) ['product_id' => 1, 'ingredient_id' => 1, 'weight' => 1]
        ];

        $this->ingredientRepository
            ->shouldReceive('findManyByProductIds')
            ->with([1])
            ->andReturn($ingredients);

        $this->ingredientProductRepository
            ->shouldReceive('findManyByProductId')
            ->with(1)
            ->andReturn($productIngredients);

        $this->ingredientService->checkAndUpdate($products);
    }

    public function testReturnEmptyEmailList()
    {
        $products = [
            ['product_id' => 1, 'quantity' => 10]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 200, 'max_stock' => 200, 'below_threshold' => false]
        ];

        $productIngredients = [
            (object) ['product_id' => 1, 'ingredient_id' => 1, 'weight' => 1]
        ];

        $this->ingredientRepository
            ->shouldReceive('findManyByProductIds')
            ->with([1])
            ->andReturn($ingredients);

        $this->ingredientProductRepository
            ->shouldReceive('findManyByProductId')
            ->with(1)
            ->andReturn($productIngredients);

        $this->ingredientRepository
            ->shouldReceive('updateMany')
            ->with(Mockery::on(function ($updatedIngredients) {
                return $updatedIngredients[0]->stock === 190; // 100 - (1 * 10)
            }));

        $emailList = $this->ingredientService->checkAndUpdate($products);

        $this->assertEmpty($emailList);
    }

    public function testReturnEmailListWithElements()
    {
        $products = [
            ['product_id' => 1, 'quantity' => 2]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 120, 'max_stock' => 200, 'below_threshold' => false]
        ];

        $productIngredients = [
            (object) ['product_id' => 1, 'ingredient_id' => 1, 'weight' => 50]
        ];

        $this->ingredientRepository
            ->shouldReceive('findManyByProductIds')
            ->with([1])
            ->andReturn($ingredients);

        $this->ingredientProductRepository
            ->shouldReceive('findManyByProductId')
            ->with(1)
            ->andReturn($productIngredients);

        $this->ingredientRepository
            ->shouldReceive('updateMany')
            ->with(Mockery::on(function ($updatedIngredients) {
                return $updatedIngredients[0]->stock === 20; // 120 - (2 * 50)
            }));

        $emailList = $this->ingredientService->checkAndUpdate($products);

        $this->assertNotEmpty($emailList);
        $this->assertCount(1, $emailList);
        $this->assertEquals(1, $emailList[0]->id);
    }
}
