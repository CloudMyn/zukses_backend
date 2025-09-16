<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ShopIncome extends Model
{
    protected $table = 'shop_incomes';
    protected $guarded = ['id'];
}
