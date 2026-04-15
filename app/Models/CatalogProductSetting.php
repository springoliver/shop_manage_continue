<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProductSetting extends Model
{
    protected $table = 'stoma_catalog_products_settings';

    protected $primaryKey = 'settingid';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'storeid',
        'value',
    ];
}
