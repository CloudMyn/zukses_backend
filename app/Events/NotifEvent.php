<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotifEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $created_by;
    public $title;
    public $desc;
    public $to_user;
    public $to_role;

    public function __construct($created_by, $title, $desc, $to_user = null, $to_role = null)
    {
        $this->created_by = $created_by;
        $this->title = $title;
        $this->desc = $desc;
        $this->to_user = $to_user;
        $this->to_role = $to_role;
    }

    public function broadcastOn()
    {
        return ['my-channel'];
    }

    public function broadcastAs()
    {
        $data = [
            "created_by" => $this->created_by,
            "to_user" => $this->to_user,
            "to_role" => $this->to_role,
            "title" => $this->title,
            "desc" => $this->desc,
        ];
        
        Notification::create($data);

        return 'NotifEvent';
    }
}