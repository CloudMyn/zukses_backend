<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class IncomeDetail extends Model
{
    protected $table = 'income_details';
    protected $guarded = ['id'];
}
