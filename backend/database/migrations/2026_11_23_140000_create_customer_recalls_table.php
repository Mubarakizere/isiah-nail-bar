<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('last_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, spoke, no_answer, booked, do_not_contact
            $table->text('notes')->nullable();
            $table->foreignId('called_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('email_reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'last_booking_id'], 'cust_last_booking_unique');
            $table->index(['customer_id', 'status']);
            $table->index('called_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_recalls');
    }
};
