<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGame extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {

            $table->bigIncrements('id');
            
            $table->integer('user_req');
            $table->integer('user_res')->nullable();
            $table->boolean('with_bot')->nullable();
            $table->boolean('is_start')->nullable();
            $table->boolean('is_finish')->nullable();
            $table->boolean('is_waite')->nullable();
            $table->integer('rand_user')->nullable();//نوبت چه کسی است 
            $table->integer('rand_time_out')->nullable();// نوبت فیل شد 
            $table->integer('start_time')->nullable();
            $table->integer('finish_time')->nullable();
            $table->integer('game_time_out')->nullable();
            $table->integer('result')->nullable(); 
            
            $table->integer('timer_id')->nullable();  

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
        Schema::dropIfExists('games');
    }
}
