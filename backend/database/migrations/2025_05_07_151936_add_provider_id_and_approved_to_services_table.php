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
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'provider_id')) {
                $table->unsignedBigInteger('provider_id')->nullable()->after('id');
                $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            }

            // Remove the 'approved' column line if it's already in the table
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
        });
    }


};
