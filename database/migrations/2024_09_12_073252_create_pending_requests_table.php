<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('pending_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Menyimpan ID user yang terkait
            $table->enum('type', ['coach_application', 'booking_request', 'coach_schedule_change', 'class_cancellation']); // Tipe permintaan
            $table->text('details')->nullable(); // Informasi tambahan
            $table->text('reason')->nullable(); // Alasan pengajuan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status permintaan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_requests');
    }
}
