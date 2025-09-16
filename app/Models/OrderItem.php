<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'qty',
        'price',
        'address_id',
        'status',
        'original_price',
        'insurance',
        'service_fee',
        'payment_fee',
        'discount',
        'subsidy',
        'voucher',
        'shipping',
        'price_shipping',
        'courier_id',
        'resi'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the product associated with the order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
