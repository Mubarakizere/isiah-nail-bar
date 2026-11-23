<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('source', 32)->default('website')->index()->after('reference');
        });

        DB::table('bookings')
            ->where('reference', 'like', 'MAN-%')
            ->update(['source' => 'admin_manual']);

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'declined', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', 'in_progress')->update(['status' => 'accepted']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'accepted', 'declined', 'completed', 'cancelled') DEFAULT 'pending'");
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });

        // Keep nullable email if walk-in customers without email have been recorded.
        if (!DB::table('customers')->whereNull('email')->exists()) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('email')->nullable(false)->change();
            });
        }
    }
};
