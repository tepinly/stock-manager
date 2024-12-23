<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('weight');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity');
    }

    public function ingredientProduct()
    {
        return $this->hasMany(IngredientProduct::class, 'product_id');
    }

    public function orderProduct()
    {
        return $this->hasMany(OrderProduct::class);
    }
}
