<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;

Route::post('/orders', [OrderController::class, 'create']);
