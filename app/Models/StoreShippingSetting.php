<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreShippingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'is_store_courier_active',
        'distance_tiers',
        'weight_tiers',
        'enabled_service_ids',
        'max_distance',
        'max_weight'
    ];

    protected $casts = [
        'is_store_courier_active' => 'boolean',
        'distance_tiers' => 'array',
        'weight_tiers' => 'array',
        'enabled_service_ids' => 'array',
        'max_distance' => 'integer',
        'max_weight' => 'integer'
    ];
}
