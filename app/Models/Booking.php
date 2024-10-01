<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'user_id', 'booking_date','booking_code', 'amount', 'paid', 'scanned'
];

public function class()
{
    return $this->belongsTo(Classes::class);
}

public function member()
{
    return $this->belongsTo(User::class, 'user_id');
}
public function coach()
{
    return $this->belongsTo(User::class, 'coach_id');
}
}