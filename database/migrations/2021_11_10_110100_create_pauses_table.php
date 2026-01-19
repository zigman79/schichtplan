<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePausesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pausen', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // pause Times connected to the arbeitszeiten
            $table->unsignedBigInteger('arbeitszeit_id');
            $table->foreign('arbeitszeit_id')->references('id')->on('arbeitszeiten');

            $table->time('beginn');
            $table->time('ende')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pausen');
    }
}
