<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_id', 'total_price', 'status', 'user_profile_id'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        // Relasi ke tabel order_items, foreign key-nya adalah 'order_id'
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function userProfile()
    {
        // Relasi ke tabel user_profiles, foreign key 'user_profile_id'
        // akan merujuk ke primary key 'id' di tabel user_profiles.
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }
}
