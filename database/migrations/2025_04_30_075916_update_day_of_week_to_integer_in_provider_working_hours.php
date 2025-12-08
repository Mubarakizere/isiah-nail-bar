<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('provider_working_hours', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week_int')->nullable(); // temp column
        });

        // Optional: Migrate existing values from string to int
        DB::table('provider_working_hours')->get()->each(function ($record) {
            $dayMap = [
                'sunday' => 0, 'monday' => 1, 'tuesday' => 2,
                'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6
            ];

            DB::table('provider_working_hours')
                ->where('id', $record->id)
                ->update(['day_of_week_int' => $dayMap[strtolower($record->day_of_week)] ?? null]);
        });

        // Drop old column and rename new one
        Schema::table('provider_working_hours', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
            $table->renameColumn('day_of_week_int', 'day_of_week');
        });
    }

    public function down()
    {
        Schema::table('provider_working_hours', function (Blueprint $table) {
            $table->string('day_of_week')->nullable();
        });

        // Optional: reverse mapping logic here

        Schema::table('provider_working_hours', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }

};
