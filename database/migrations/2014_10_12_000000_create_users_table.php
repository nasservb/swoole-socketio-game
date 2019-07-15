<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

 



        Schema::create('users', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('api_token');
            $table->string('full_name')->nullable();
            $table->string('ip')->nullable();
            $table->string('device_id')->nullable();
            $table->integer('coin_count')->nullable();
            $table->string('card_number')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('referrer_code')->nullable();
            $table->boolean('is_register')->nullable();
            $table->boolean('is_join_telegram')->nullable();
            $table->boolean('is_join_instagram')->nullable();
            $table->boolean('is_man')->nullable();
            $table->integer('rank')->nullable();
            $table->integer('xp')->nullable(); 
            $table->integer('seq')->nullable(); 
            
            $table->integer('active_game_id')->nullable(); 

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
