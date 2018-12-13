<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetPlaylistUserOwnerColumnDefaultToZero extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playlist_user', function (Blueprint $table) {
            $table->boolean('owner')->unsigned()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlist_user', function (Blueprint $table) {
            $table->boolean('owner')->unsigned()->default(1)->change();
        });
    }
}
