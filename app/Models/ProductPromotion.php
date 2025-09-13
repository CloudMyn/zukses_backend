<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPromotion extends Model
{
    protected $guarded = ['id'];

    public function promotionPrice()
    {
        return $this->belongsTo(
            ProductVariantPrice::class,
            'product_variant_price_id'
        );
    }
}
