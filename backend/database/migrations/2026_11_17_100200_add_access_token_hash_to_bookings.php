<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'access_token_hash')) {
                $table->char('access_token_hash', 64)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'access_token_hash')) {
                $table->dropIndex(['access_token_hash']);
                $table->dropColumn('access_token_hash');
            }
        });
    }
};
