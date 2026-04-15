<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductPaymentMethod extends Model
{
    protected $table = 'stoma_catalog_products_payment_methods';
    protected $primaryKey = 'payment_methodid';
    public $timestamps = false;

    protected $fillable = [
        'storeid',
        'payment_method',
        'email',
        'merchantid',
        'currency',
        'mode',
        'status',
        'insertdate',
        'insertip',
        'insertby',
        'editdate',
        'editip',
    ];
}

