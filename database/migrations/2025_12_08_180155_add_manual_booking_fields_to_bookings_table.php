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
        Schema::table('bookings', function (Blueprint $table) {
            // Add missing fields for manual booking
            $table->string('payment_option')->default('full')->after('time');
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('payment_option');
            $table->string('reference')->nullable()->after('deposit_amount');
            
            // Make service_id nullable since we use many-to-many relationship
            $table->foreignId('service_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_option', 'deposit_amount', 'reference']);
        });
    }
};
