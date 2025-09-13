<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequermentProduct extends Model
{
    protected $table = 'requerment_products';
    protected $guarded = ['id'];

    protected $casts = [
        'image_variant' => 'array',
    ];
}
