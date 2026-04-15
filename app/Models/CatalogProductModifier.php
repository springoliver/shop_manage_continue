<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductModifier extends Model
{
    protected $table = 'stoma_catalog_products_modifiers';
    protected $primaryKey = 'modifier_id';
    public $timestamps = false;

    protected $fillable = [
        'modifier_name',
        'modifier_price',
        'storeid',
        'modifier_status',
        'income_sum',
        'profit_percentage',
        'insertdate',
        'insertip',
        'insertby',
        'editdate',
        'editip',
    ];
}

