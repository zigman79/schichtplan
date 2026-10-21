<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ohne Unique-Index: ALTER TABLE users ist auf dem Live-Server dauerhaft mit
     * "Lock wait timeout" gescheitert. Die Eindeutigkeit stellt
     * UserController::generatePermalink sicher.
     *
     * hasColumn: Die Spalte wurde beim ersten, abgebrochenen Lauf schon angelegt.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'permalink_token')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('permalink_token', 64)->nullable()->after('api_qr_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permalink_token');
        });
    }
};
