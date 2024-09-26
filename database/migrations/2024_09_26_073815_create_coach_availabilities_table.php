<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coach_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Menggunakan user_id dari tabel users
            $table->date('date'); // Tanggal availabilitas
            $table->time('start_time'); // Waktu mulai availabilitas
            $table->time('end_time'); // Waktu akhir availabilitas
            $table->timestamps();
        
            // Relasi ke tabel users (role coach)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_availabilities');
    }
};
