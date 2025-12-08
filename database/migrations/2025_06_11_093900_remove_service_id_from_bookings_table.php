<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveServiceIdFromBookingsTable extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['service_id']); // Drop foreign key first
            $table->dropColumn('service_id');    // Then drop the column
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
        });
    }
}
