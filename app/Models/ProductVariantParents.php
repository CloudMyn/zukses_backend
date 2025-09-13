<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantParents extends Model
{
    protected $table = 'product_variant_parents';

    protected $fillable = ['product_id', 'name', 'ordinal'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
