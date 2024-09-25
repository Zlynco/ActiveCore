<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coach_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade'); // Asumsikan coach adalah user dengan role coach
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User yang membooking coach
            $table->foreignId('availability_id')->nullable()->constrained('coach_availability')->onDelete('set null');
            $table->integer('session_count')->default(0); // Menghitung jumlah sesi yang telah dibooking
            $table->timestamp('last_booking_date')->nullable(); // Tanggal booking terakhir
            $table->dropColumn('booking_code');
            $table->boolean('payment_required')->default(false); // Flag untuk menandai apakah pembayaran diperlukan setelah 4 sesi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_bookings');
    }
};