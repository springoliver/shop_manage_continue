<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductIngredient extends Model
{
    protected $table = 'stoma_catalog_product_ingredients';

    protected $primaryKey = 'catalog_product_ingredient_id';

    public $timestamps = false;

    protected $fillable = [
        'storeid',
        'store_product_id',
        'catalog_product_id',
        'percentage',
        'price',
        'insertdate',
        'insertip',
        'insertby',
    ];
}
