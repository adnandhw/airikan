<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    use HasFactory, \App\Traits\HasStringId;

    protected $casts = [
        'buyer_info' => 'array',
        'products' => 'array',
        'is_stock_deducted' => 'boolean',
    ];

    // protected $connection = 'mysql';

    protected $fillable = [
        'buyer_id',
        'buyer_info', // Stores snapshot of buyer details (name, phone, address)
        'products',   // Stores array of purchased products (snapshot)
        'total_amount',
        'shipping_cost',
        'courier_name',
        'total_weight',
        'distance',
        'total_payment',
        'status',     // pending, paid, shipped, completed, cancelled
        'payment_proof', // path/url to image
        'short_id', // 8-char uppercase ID for easy searching
        'is_stock_deducted',
        'shipping_receipt_number',
    ];
}
