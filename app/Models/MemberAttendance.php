<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MemberAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'member_id',
        'coach_id',
        'attendance_date',
        'unique_code',
        'status',
    ];

    public function booking()
    {
        return $this->belongsTo(CoachBooking::class);
    }    

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
    public static function boot()
{
    parent::boot();

    static::creating(function ($attendance) {
        if (is_null($attendance->unique_code)) {
            $attendance->unique_code = Str::random(10); // Mengenerate kode unik
        }
    });
}

}