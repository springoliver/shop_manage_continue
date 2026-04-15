<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductAddon extends Model
{
    protected $table = 'stoma_catalog_products_addons';
    protected $primaryKey = 'addonid';
    public $timestamps = false;

    protected $fillable = [
        'storeid',
        'addon',
        'price',
        'product_categoryid',
        'product_groupid',
        'addon_status',
        'insertdate',
        'insertip',
        'insertby',
        'editdate',
        'editip',
    ];
}

