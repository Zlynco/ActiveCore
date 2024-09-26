<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

// Model CoachBooking
class CoachBooking extends Model
{
    protected $fillable = ['user_id', 'coach_id', 'session_count', 'booking_date', 'start_booking_time','end_booking_time', 'payment_required', 'booking_code', 'availability_id'];
    // Relasi dengan member (User yang melakukan booking)
    public function member()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Relasi ke Coach
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Cek apakah pembayaran diperlukan
    public function isPaymentRequired()
    {
        return $this->session_count % 4 == 0;
    }
    protected $dates = ['last_booking_date'];

    public function getFormattedLastBookingDateAttribute()
    {
        return Carbon::parse($this->last_booking_date)->format('Y-m-d');
    }

}
