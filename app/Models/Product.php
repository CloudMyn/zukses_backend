<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'desc',
        'sku',
        'price',
        'stock',
        'min_purchase',
        'max_purchase',
        'is_used',
        'scheduled_date',
        'is_cod_enabled',
        'image',
        'voucher'
    ];
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function shopProfile()
    {
        return $this->belongsTo(ShopProfile::class, 'seller_id');
    }
    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }
    public function delivery()
    {
        return $this->hasOne(ProductDelivery::class);
    }
    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function variantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
    public function promotion()
    {
        return $this->hasOne(ProductPromotion::class);
    }
}
