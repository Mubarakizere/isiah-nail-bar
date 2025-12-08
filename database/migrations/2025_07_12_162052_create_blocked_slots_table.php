<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->timestamps();

            $table->unique(['provider_id', 'date', 'time'], 'unique_block_per_slot');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_slots');
    }
}
