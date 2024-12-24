<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientProduct extends Model
{
    protected $table = 'ingredient_product';
    protected $fillable = ['ingredient_id', 'product_id', 'weight'];
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;
    
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
