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
            $table->unsignedBigInteger('coach_id');
            $table->string('day_of_week'); // Hari kelas atau booking (misal: Monday, Tuesday)
            $table->date('date')->nullable(); // Tanggal spesifik untuk booking
            $table->time('time')->nullable(); // Waktu kelas/booking
            $table->boolean('status')->default(1); // 1 = available, 0 = unavailable
            $table->timestamps();
        
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
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
