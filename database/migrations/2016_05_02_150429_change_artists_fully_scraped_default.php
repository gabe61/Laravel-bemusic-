<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArtistsFullyScrapedDefault extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $table = $prefixed = DB::getTablePrefix() ? DB::getTablePrefix().'artists' : 'artists';
        DB::statement("ALTER TABLE `$table` CHANGE COLUMN `fully_scraped` `fully_scraped` tinyint(1) NOT NULL DEFAULT '0';");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
