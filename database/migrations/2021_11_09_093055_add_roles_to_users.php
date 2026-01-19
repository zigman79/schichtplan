<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRolesToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // add two columns for admin and teamleader role
            $table->boolean('arbeitszeit_admin')->default(false);
            $table->boolean('arbeitszeit_teamleader')->default(false);
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
            // remove the two columns for admin and teamleader role
            $table->dropColumn('arbeitszeit_admin');
            $table->dropColumn('arbeitszeit_teamleader');
        });
    }
}
