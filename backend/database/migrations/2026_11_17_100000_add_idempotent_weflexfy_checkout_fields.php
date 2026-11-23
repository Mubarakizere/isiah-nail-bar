<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'checkout_key')) {
                $table->uuid('checkout_key')->nullable()->unique();
            }
            if (!Schema::hasColumn('payments', 'payment_url')) {
                $table->text('payment_url')->nullable();
            }
        });

    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'checkout_key')) {
                $table->dropUnique(['checkout_key']);
                $table->dropColumn('checkout_key');
            }
            if (Schema::hasColumn('payments', 'payment_url')) {
                $table->dropColumn('payment_url');
            }
        });
    }
};
