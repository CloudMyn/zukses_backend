<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_addresses';
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'province_id' => 'integer',
        'citie_id' => 'integer',
        'subdistrict_id' => 'integer',
        'postal_code_id' => 'integer',
        'lat' => 'double',
        'long' => 'double',
        'is_primary' => 'integer',
        'is_store' => 'integer',
    ];
}
