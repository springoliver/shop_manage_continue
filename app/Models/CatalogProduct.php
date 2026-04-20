<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProduct extends Model
{
    protected $table = 'stoma_catalog_products';

    protected $primaryKey = 'catalog_product_id';

    public $timestamps = false;

    protected $fillable = [
        'catalog_product_name',
        'catalog_product_price',
        'catalog_product_desc',
        'catalog_product_photo',
        'storeid',
        'catalog_product_groupid',
        'catalog_product_categoryid',
        'catalog_product_status',
        'income_sum',
        'profit_percentage',
        'insertdate',
        'insertip',
        'insertby',
        'editdate',
        'editip',
    ];
}
