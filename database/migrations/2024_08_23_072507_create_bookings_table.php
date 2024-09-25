<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('user_id'); // ID member yang melakukan booking
            $table->timestamp('booking_date'); // Tanggal/waktu booking
            $table->decimal('amount', 8, 2)->nullable(); // Jumlah pembayaran
            $table->boolean('paid')->default(false); // Status pembayaran
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->decimal('amount', 8, 2)->nullable(); // Jumlah pembayaran
            $table->boolean('paid')->default(false); // Status pembayaran
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
}

