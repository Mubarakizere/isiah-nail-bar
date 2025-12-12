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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_type')->default('customer'); // customer, provider, admin
            $table->string('subject');
            $table->string('email_type'); // booking_confirmation, booking_reminder, booking_cancelled, etc.
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('sent'); // sent, failed, pending
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('recipient_email');
            $table->index('email_type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
