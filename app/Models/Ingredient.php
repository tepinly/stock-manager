<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'stock',
        'max_stock',
        'below_threshold',
    ];

    public function product()
    {
        return $this->belongsToMany(Product::class)->withPivot('weight');
    }

    public function ingredientProduct()
    {
        return $this->hasMany(IngredientProduct::class, 'ingredient_id');
    }
}
