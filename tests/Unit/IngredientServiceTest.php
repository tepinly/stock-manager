<?php

namespace Tests\Unit;

use App\Mail\IngredientBelowThreshold;
use App\Repositories\IIngredientProductRepository;
use App\Repositories\IIngredientRepository;
use App\Services\IngredientService;
use Illuminate\Support\Facades\Mail;
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

    public function testUpdateStockAndSendEmail()
    {
        Mail::fake();

        $products = [
            ['product_id' => 1, 'quantity' => 5]
        ];

        $ingredients = [
            (object) ['id' => 1, 'stock' => 50, 'max_stock' => 100, 'below_threshold' => false]
        ];

        $productIngredients = [
            (object) ['product_id' => 1, 'ingredient_id' => 1, 'weight' => 2]
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
            ->with(Mockery::on(function ($updatedIngredients) use ($ingredients) {
                return $updatedIngredients[0]->stock === 40;
            }));

        $this->ingredientService->checkAndUpdate($products);

        Mail::assertSent(IngredientBelowThreshold::class, function ($mail) use ($ingredients) {
            return $mail->ingredient->id === $ingredients[0]->id;
        });
    }

    public function testSendEmailWhenBelowThreshold()
    {
        Mail::fake();

        $ingredient = (object) ['id' => 1, 'stock' => 10, 'max_stock' => 100, 'below_threshold' => false];

        $this->ingredientService->checkThreshold($ingredient);

        $this->assertTrue($ingredient->below_threshold);

        Mail::assertSent(IngredientBelowThreshold::class, function ($mail) use ($ingredient) {
            return $mail->ingredient->id === $ingredient->id;
        });
    }
}
