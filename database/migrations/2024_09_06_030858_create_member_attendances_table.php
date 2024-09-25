<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('coach_bookings')->onDelete('cascade'); // Booking coach yang dilakukan oleh member
            $table->foreignId('member_id')->constrained('users')->onDelete('cascade'); // User dengan role member
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade'); // User dengan role coach
            $table->timestamp('attendance_date');
            $table->string('unique_code')->nullable();
            $table->enum('status', ['Not Yet', 'Present', 'Absent'])->default('Not Yet')->after('unique_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_attendances');
    }
};