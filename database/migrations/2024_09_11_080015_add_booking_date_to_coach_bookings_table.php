<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingDateToCoachBookingsTable extends Migration
{
    public function up()
    {
        Schema::table('coach_bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('session_count');
        });
    }

    public function down()
    {
        Schema::table('coach_bookings', function (Blueprint $table) {
            $table->dropColumn('booking_date');
        });
    }
}
