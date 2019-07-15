<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_requests', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->integer('user_id');
            $table->integer('time');
            $table->integer('time_out')->nullable();
            $table->integer('rank')->nullable();
            $table->boolean('is_done')->nullable(); 
            $table->boolean('with_bot')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_requests');
    }
}
