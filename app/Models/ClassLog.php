<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassLog extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'action', 'changes', 'user_id'];
    
    // Definisikan relasi dengan kelas
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    // Definisikan relasi dengan user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
