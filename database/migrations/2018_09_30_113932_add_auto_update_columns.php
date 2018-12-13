<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoUpdateColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('artists', 'auto_update')) {
            Schema::table('artists', function (Blueprint $table) {
                $table->boolean('auto_update')->default(1);
            });
        }

        if ( ! Schema::hasColumn('albums', 'auto_update')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->boolean('auto_update')->default(1);
            });
        }

        if ( ! Schema::hasColumn('tracks', 'auto_update')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->boolean('auto_update')->default(1);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('auto_update');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('auto_update');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('auto_update');
        });
    }
}
