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
        if (Schema::hasColumn('bookings', 'pickup_location_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Remove orphaned columns from previous failed migration
                $table->dropColumn(['pickup_location_id', 'pickup_fee', 'pickup_address']);
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('pickup_location_id')->nullable()->constrained('pickup_locations')->nullOnDelete();
            $table->decimal('pickup_fee', 10, 2)->default(0);
            $table->text('pickup_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['pickup_location_id']);
            $table->dropColumn(['pickup_location_id', 'pickup_fee', 'pickup_address']);
        });
    }
};
