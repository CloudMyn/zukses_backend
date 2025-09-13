<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariationOption extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_options')
            ->withTimestamps(); // Memberitahu Eloquent tentang timestamps di tabel pivot
    }
}
