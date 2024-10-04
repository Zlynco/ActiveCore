<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'day_of_week',
        'start_time',
        'end_time',
        'image',
        'price',
        'coach_id',
        'category_id',
        'quota',
        'current_bookings',
        'room',
        'room_id',
    ];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id')->where('role', 'coach');
    }

    // Jika ingin memastikan data class tersedia
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'class_id');
    }
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function getFormattedTimeAttribute()
    {
        return $this->time->format('H:i');
    }
    public function availableQuotaForDate($date)
    {
        $totalQuota = $this->quota; // Kuota global
        $totalBooked = Booking::where('class_id', $this->id)
                              ->whereDate('booking_date', $date)
                              ->count();

        return $totalQuota - $totalBooked;
    }
    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

}

