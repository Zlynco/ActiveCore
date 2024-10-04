<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            // Menghapus kolom yang tidak diperlukan
            $table->dropColumn(['start_time', 'end_time']);

            // Menambahkan kolom baru
            $table->time('start_time'); // Jam mulai
            $table->time('end_time');   // Jam selesai
            $table->string('room'); // Kolom untuk ruangan
            $table->unsignedBigInteger('room_id')->nullable(); // Kolom untuk ID ruangan
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null'); // Relasi ke tabel rooms
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            // Menghapus kolom yang ditambahkan
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_id', 'room', 'start_time', 'end_time']);

            // Menambahkan kembali kolom yang dihapus
            $table->start_time('time'); // Kolom untuk jam mulai
            $table->end_time('time');   // Kolom untuk jam selesai
        });
    }
}
