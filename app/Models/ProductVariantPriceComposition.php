<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPriceComposition extends Model
{
    protected $fillable = ['product_variant_price_id', 'product_variant_value_id'];
    public function value()
    {
        return $this->belongsTo(ProductVariantValue::class, 'product_variant_value_id');
    }
}
