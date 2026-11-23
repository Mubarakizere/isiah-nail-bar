<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_service', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_service', 'price_at_booking')) {
                $table->decimal('price_at_booking', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('booking_service', 'duration_minutes_at_booking')) {
                $table->unsignedInteger('duration_minutes_at_booking')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_service', function (Blueprint $table) {
            if (Schema::hasColumn('booking_service', 'price_at_booking')) {
                $table->dropColumn('price_at_booking');
            }
            if (Schema::hasColumn('booking_service', 'duration_minutes_at_booking')) {
                $table->dropColumn('duration_minutes_at_booking');
            }
        });
    }
};
