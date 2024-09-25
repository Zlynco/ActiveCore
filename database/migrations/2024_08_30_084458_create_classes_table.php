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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable(); // Kolom untuk gambar
            $table->string('name');
            $table->text('description');
            $table->string('day_of_week'); // Tambahkan hari jadwal kelas (misalnya: Senin)
            $table->time('time');  
            $table->decimal('price', 8, 2); // Kolom untuk harga
            $table->unsignedBigInteger('coach_id'); // ID coach dari tabel users
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade'); // Relasi ke tabel users
            $table->unsignedInteger('quota')->default(10); // Set default quota to 10
            $table->unsignedInteger('current_bookings')->default(0); // Default bookings to 0
            $table->timestamps();
        });
    }
    
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
