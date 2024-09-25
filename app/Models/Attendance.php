<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'user_id',
        'attendance_date',
        'unique_code',
        'absence_reason',
        'check_in',
        'check_out',
        'status',
    ];

public function class()
{
    return $this->belongsTo(Classes::class, 'class_id'); // Pastikan nama model dan foreign key sesuai
}

public function coach()
{
    return $this->belongsTo(User::class, 'user_id'); // Menghubungkan ke tabel `users`, tempat data coach berada
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


protected $casts = [
    'attendance_date' => 'datetime',
];
}
