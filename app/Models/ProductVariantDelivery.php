<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantDelivery extends Model
{
    protected $fillable = [
        'product_variant_price_id',
        'weight',
        'length',
        'width',
        'height',
    ];

    public function variantPrice()
    {
        return $this->belongsTo(
            ProductVariantPrice::class,
            'product_variant_price_id'
        );
    }
}
