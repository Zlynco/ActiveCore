<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_attendances', function (Blueprint $table) {
            // Tambahkan status "Not Yet" sebagai default
            $table->enum('status', ['Not Yet', 'Present', 'Absent', 'Late'])->default('Not Yet')->after('unique_code');
        });
    }

    public function down(): void
    {
        Schema::table('member_attendances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
