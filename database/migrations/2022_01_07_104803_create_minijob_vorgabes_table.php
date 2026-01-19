<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinijobVorgabesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('minijob_vorgaben', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('year');
            $table->string('month');
            $table->float('hours', 8, 2);
            $table->float('away', 8, 2);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('minijob_vorgaben');
    }
}
