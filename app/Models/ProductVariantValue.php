<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantValue extends Model
{
    protected $fillable = ['variant_id', 'value', 'ordinal'];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
