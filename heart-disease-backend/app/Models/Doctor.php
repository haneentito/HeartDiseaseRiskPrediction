<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Doctor extends Authenticatable implements JWTSubject
{
    use HasFactory,Notifiable;

    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doctor_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'rating',
        'views',
        'price',
        'languages',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'rating' => 'float',
        'views' => 'integer',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'doctor_id', 'doctor_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'doctor_id', 'doctor_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'doctor_id', 'doctor_id');
    }
}