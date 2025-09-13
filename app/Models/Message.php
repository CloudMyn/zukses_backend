<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // Pastikan nama tabel benar jika berbeda dari 'messages'
    protected $table = 'messages'; 

    protected $fillable = [
        'user_id',
        'text',
        'user_profile_id', // Tambahkan ini
        'product_id',      // Tambahkan ini
        'variant_price_id',// Tambahkan ini
    ];

    // Pastikan timestamps otomatis diaktifkan (default Lumen, tapi baik untuk eksplisit)
    public $timestamps = true;
}
