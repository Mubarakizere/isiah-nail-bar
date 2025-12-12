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
        Schema::table('payments', function (Blueprint $table) {
            // Store the actual payment method used (may differ from user's initial selection)
            $table->enum('actual_method_used', ['momo', 'airtel', 'card', 'cash'])->nullable()->after('method');
            
            // Store WeFlexfy's transaction ID for reference
            $table->string('provider_transaction_id')->nullable()->after('transaction_id');
            
            // Store full webhook payload as JSON for audit trail
            $table->json('webhook_payload')->nullable()->after('provider_transaction_id');
            
            // Add index for better query performance
            $table->index('provider_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['provider_transaction_id']);
            $table->dropColumn(['actual_method_used', 'provider_transaction_id', 'webhook_payload']);
        });
    }
};
