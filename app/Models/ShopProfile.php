<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProfile extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'shop_name',
        'full_name',
        'nik',
        'ktp_url',
        'selfie_url',
        'description',
        'logo_url',
    ];

    public function shopAddresses()
    {
        return $this->hasMany(ShopAddress::class, 'seller_id');
    }

    public function getPrimaryShopAddressAttribute()
    {
        $primary = $this->shopAddresses->where('is_primary', 1)->first();
        return $primary ?: $this->shopAddresses->first(); // fallback ke data awal jika tidak ada yang is_primary
    }
}
