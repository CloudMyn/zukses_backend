<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierService extends Model
{
    use HasFactory;

    protected $fillable = ['courier_id', 'code', 'name', 'is_active'];

    /**
     * Get the courier that owns the service.
     */
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}
