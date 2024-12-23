<?php

namespace App\Providers;

use App\Repositories\IIngredientRepository;
use App\Repositories\IngredientRepository;
use App\Repositories\IIngredientProductRepository;
use App\Repositories\IngredientProductRepository;
use App\Repositories\IOrderRepository;
use App\Repositories\OrderRepository;
use App\Services\IngredientService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IOrderRepository::class, OrderRepository::class);
        $this->app->bind(IIngredientRepository::class, IngredientRepository::class);
        $this->app->bind(IIngredientProductRepository::class, IngredientProductRepository::class);

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService($app->make(IOrderRepository::class), $app->make(IngredientService::class));
        });
        $this->app->bind(IngredientService::class, function ($app) {
            return new IngredientService($app->make(IIngredientRepository::class), $app->make(IIngredientProductRepository::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
