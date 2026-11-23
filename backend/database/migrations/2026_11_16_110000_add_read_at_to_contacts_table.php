<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->index()->after('message');
        });

        // Existing messages have no read history, so treat them as inbox history.
        DB::table('contacts')->whereNull('read_at')->update(['read_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
            $table->dropColumn('read_at');
        });
    }
};
