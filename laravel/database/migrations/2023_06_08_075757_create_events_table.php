<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('game_code', 10);
            $table->string('name', 100);
            $table->text('value');
            $table->dateTime('log_time');
            $table->timestamps();

            $table->foreign('game_code')->references('code')->on('games')->onUpdate('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
