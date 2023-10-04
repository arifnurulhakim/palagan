<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('game_code', 10);
            $table->string('currency');
            $table->decimal('amount', 10, 2);
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
        Schema::dropIfExists('wallets');
    }
}
