<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArbeitszeitenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arbeitszeiten', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->date('tag');
            $table->time('beginn')->nullable();
            $table->time('ende')->nullable();
            $table->enum('frei_urlaub_krank', ['frei', 'urlaub', 'krank' ,'schule'])->nullable();
            $table->float('arbeitszeit')->nullable();
            $table->boolean('beginn_bestaetigt')->default(false);
            $table->boolean('ende_bestaetigt')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arbeitszeiten');
    }
}
