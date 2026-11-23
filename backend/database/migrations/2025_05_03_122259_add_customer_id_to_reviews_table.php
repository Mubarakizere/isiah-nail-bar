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
    Schema::table('reviews', function (Blueprint $table) {
        $table->unsignedBigInteger('customer_id')->after('service_id');

        // Optional: Add foreign key if you have a customers table
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn('customer_id');
    });
}

};
