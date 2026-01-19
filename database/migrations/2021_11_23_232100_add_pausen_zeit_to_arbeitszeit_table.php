<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPausenZeitToArbeitszeitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('arbeitszeiten', function (Blueprint $table) {
            $table->integer('pausenzeit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('arbeitszeiten', function (Blueprint $table) {
            $table->dropColumn('pausenzeit');
        });
    }
}
