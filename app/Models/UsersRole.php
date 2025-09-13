<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersRole extends Model
{
    protected $table = 'users_role';
    protected $guarded = ['id'];

    public function access()
    {
        return $this->hasMany(UsersAccessMenu::class);
    }
}
