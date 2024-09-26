<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToUsersAndClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Menambahkan category_id pada tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('role');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        // Menambahkan category_id pada tabel classes
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('coach_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop kolom category_id jika rollback
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
