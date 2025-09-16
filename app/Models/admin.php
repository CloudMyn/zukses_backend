<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    JWTSubject // ⬅️ tambahkan ini
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'id',
        'whatsapp',
        'role',
        'start_date',
        'end_date',
        'status'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * JWTSubject method: return identifier (biasanya ID user)
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWTSubject method: tambahkan claim custom jika perlu
     */
    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'role'  => $this->role,
        ];
    }
}
