<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkinghoursUsersPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arbeitszeiten_team', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // connect users using pivot table
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('arbeitszeit_user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('arbeitszeit_user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arbeitszeiten_team');
    }
}
