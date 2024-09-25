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
        Schema::create('class_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id'); // ID kelas yang terlibat
            $table->string('action'); // Jenis aksi (create, update, delete)
            $table->text('changes')->nullable(); // Deskripsi perubahan (JSON format)
            $table->unsignedBigInteger('user_id'); // ID user yang melakukan aksi
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_logs');
    }
};
