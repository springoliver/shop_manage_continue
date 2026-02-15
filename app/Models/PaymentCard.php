<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{
    use HasFactory;

    protected $table = 'stoma_payment_card';
    protected $primaryKey = 'cardid';
    public $timestamps = false;

    protected $fillable = [
        'storeid',
        'ownerid',
        'name_on_card',
        'card_last4',
        'card_brand',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'expiry_month',
        'expiry_year',
        'status',
        'insertdate',
        'insertip',
        'editdate',
        'editip',
    ];
}
