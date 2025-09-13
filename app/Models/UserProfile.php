<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'date_birth',
        'name_store',
        'image',
    ];
}
