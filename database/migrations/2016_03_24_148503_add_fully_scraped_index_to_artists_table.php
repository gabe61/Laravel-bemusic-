<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFullyScrapedIndexToArtistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('artists', function(Blueprint $table)
		{
			$table->index('fully_scraped');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('artists', function(Blueprint $table)
		{
			$table->dropIndex('fully_scraped');
		});
	}

}
