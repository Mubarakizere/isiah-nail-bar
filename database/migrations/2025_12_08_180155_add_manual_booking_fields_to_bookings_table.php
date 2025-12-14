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
            if (!Schema::hasColumn('bookings', 'payment_option')) {
                $table->string('payment_option')->default('full')->after('time');
            }
            if (!Schema::hasColumn('bookings', 'deposit_amount')) {
                $table->decimal('deposit_amount', 10, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('bookings', 'reference')) {
                $table->string('reference')->nullable()->after('status');
            }
            
            // Make service_id nullable since we use many-to-many relationship
            // $table->foreignId('service_id')->nullable()->change(); // This might fail if constraint exists, keeping it safe
        });
        
        // Separate alteration for service_id to avoid issues if previous block partially ran
        if (Schema::hasColumn('bookings', 'service_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                 $table->foreignId('service_id')->nullable()->change();
            });
        }
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
