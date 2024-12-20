<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'profile_image',
        'email_verified_at',
        'password',
        'category_id',
        'role',
    ];
    // Relasi dengan Booking
    public function booking()
    {
        return $this->hasMany(Booking::class, 'user_id');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'member_id', 'id');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    // App\Models\User.php

    public function classes()
    {
        return $this->hasMany(Classes::class, 'coach_id');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_coach');
    }
    // Dalam model User.php
public function scopeCoaches($query)
{
    return $query->where('role', 'coach');
}

}
