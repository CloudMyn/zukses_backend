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
}
