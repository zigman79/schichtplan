<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Übertrag der Standard-Minijobber zum 1.1. in Stunden. NULL heißt: aus dem Vorjahr berechnen.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('start_values', 'minijob_uebertrag')) {
            return;
        }

        Schema::table('start_values', function (Blueprint $table) {
            $table->double('minijob_uebertrag', 8, 2)->nullable()->after('resturlaub_aktuell');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('start_values', function (Blueprint $table) {
            $table->dropColumn('minijob_uebertrag');
        });
    }
};
