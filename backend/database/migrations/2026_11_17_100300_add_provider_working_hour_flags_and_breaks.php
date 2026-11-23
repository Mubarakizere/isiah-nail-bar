<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_working_hours', function (Blueprint $table) {
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();

            if (!Schema::hasColumn('provider_working_hours', 'break_start')) {
                $table->time('break_start')->nullable();
            }
            if (!Schema::hasColumn('provider_working_hours', 'break_end')) {
                $table->time('break_end')->nullable();
            }
            if (!Schema::hasColumn('provider_working_hours', 'is_day_off')) {
                $table->boolean('is_day_off')->default(false);
            }
            if (!Schema::hasColumn('provider_working_hours', 'is_holiday')) {
                $table->boolean('is_holiday')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('provider_working_hours', function (Blueprint $table) {
            foreach (['break_start', 'break_end', 'is_day_off', 'is_holiday'] as $column) {
                if (Schema::hasColumn('provider_working_hours', $column)) {
                    $table->dropColumn($column);
                }
            }

            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
