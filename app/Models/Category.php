<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Relasi ke `User` (coach) berdasarkan kategori.
     */
    public function coaches()
    {
        return $this->hasMany(User::class, 'category_id');
    }

    /**
     * Relasi ke `Classes` berdasarkan kategori.
     */
    public function classes()
    {
        return $this->hasMany(Classes::class, 'category_id');
    }
}
