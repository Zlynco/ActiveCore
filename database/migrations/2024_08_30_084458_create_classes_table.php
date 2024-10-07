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
            $table->text('description')->nullable(); // Deskripsi kelas, bisa null
            $table->string('day_of_week'); // Hari jadwal kelas (misalnya: Senin)
            $table->date('date'); // Tanggal kelas
            $table->time('start_time'); // Jam mulai
            $table->time('end_time'); // Jam selesai
            $table->decimal('price', 8, 2)->nullable(); // Kolom untuk harga, bisa null
            $table->unsignedBigInteger('coach_id'); // ID coach dari tabel users
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade'); // Relasi ke tabel users
            $table->unsignedBigInteger('category_id')->nullable(); // Kolom untuk ID kategori
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null'); // Relasi ke tabel categories
            $table->unsignedInteger('quota')->default(10); // Kuota peserta, default 10
            $table->unsignedInteger('registered_count')->default(0); // Jumlah peserta terdaftar
            $table->unsignedBigInteger('room_id')->nullable(); // Kolom untuk ID ruangan
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null'); // Relasi ke tabel rooms
            $table->enum('recurrence', ['once', 'monthly'])->default('once'); // Menyimpan jenis jadwal: 'once' atau 'monthly'
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
