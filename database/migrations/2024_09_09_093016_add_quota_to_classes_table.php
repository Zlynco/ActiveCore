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
        Schema::table('classes', function (Blueprint $table) {
            // Add the following lines to the existing up method
            $table->unsignedInteger('quota')->default(10); // Set default quota to 10
            $table->unsignedInteger('current_bookings')->default(0); // Default bookings to 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('quota');
            $table->dropColumn('current_bookings');
        });
    }
};
