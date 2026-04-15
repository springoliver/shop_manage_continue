<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductCategory extends Model
{
    protected $table = 'stoma_catalog_product_category';

    protected $primaryKey = 'catalog_product_categoryid';

    public $timestamps = false;

    protected $fillable = [
        'catalog_product_groupid',
        'catalog_product_category_name',
        'catalog_product_category_colour',
        'catalog_product_sell_online',
        'catalog_product_taxid',
        'storeid',
        'insertdate',
        'insertip',
        'insertby',
        'editdate',
        'editip',
        'editby',
    ];
}

