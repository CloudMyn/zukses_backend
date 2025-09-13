<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = ['product_id', 'image', 'price', 'stock', 'variant_code'];


    public function compositions()
    {
        return $this->hasMany(ProductVariantPriceComposition::class, 'product_variant_price_id');
    }
    public function composition()
    {
        return $this->hasMany(ProductVariantPriceComposition::class, 'product_variant_price_id');
    }
    public function delivery()
    {
        return $this->hasOne(
            ProductVariantDelivery::class,
            'product_variant_price_id'
        );
    }
    public function promotion()
    {
        return $this->hasOne(
            ProductPromotion::class,
            'product_variant_price_id'
        );
    }
}
