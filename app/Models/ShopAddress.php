<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ShopAddress extends Model
{
    protected $table = 'shop_addresses';
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'province_id' => 'integer',
        'citie_id' => 'integer',
        'subdistrict_id' => 'integer',
        'postal_code_id' => 'integer',
        'lat' => 'double',
        'long' => 'double',
        'is_primary' => 'integer',
    ];
    public function province()
    {
        return $this->belongsTo(MasterProvince::class, 'province_id');
    }
    public function cities()
    {
        return $this->belongsTo(MasterCity::class, 'citie_id');
    }
}
