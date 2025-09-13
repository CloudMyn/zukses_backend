<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $guarded = ['id'];

    public function opened($id_user)
    {
        return $this->hasOne(OpenNotification::class, 'id_notification')
                    ->where('opened_by', $id_user);
    }
}
