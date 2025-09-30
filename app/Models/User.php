<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements
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
        'on_board',
        'start',
        'expierd',
        'status'
    ];

    protected $hidden = [
        'password',
    ];

    protected $with = [
        'profile',
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
            'role' => $this->role,
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}
