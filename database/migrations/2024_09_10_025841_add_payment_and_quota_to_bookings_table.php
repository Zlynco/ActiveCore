<?php

// database/migrations/xxxx_xx_xx_add_payment_and_quota_to_bookings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentAndQuotaToBookingsTable extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->nullable(); // Jumlah pembayaran
            $table->boolean('paid')->default(false); // Status pembayaran
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('paid');
        });
    }
}