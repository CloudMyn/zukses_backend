<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersAccessMenu extends Model
{
    protected $table = 'users_access_menu';
    protected $guarded = ['id'];

    public function menu()
    {
        return $this->belongsTo(UsersMenu::class);
    }
}
