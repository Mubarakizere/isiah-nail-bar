<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('reminder_delivery_status')->nullable();
            $table->timestamp('reminder_failed_at')->nullable();
            $table->index(['status', 'date', 'reminder_sent'], 'bookings_reminder_candidates_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_reminder_candidates_index');
            $table->dropColumn(['reminder_delivery_status', 'reminder_failed_at']);
        });
    }
};
