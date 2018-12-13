<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWikiImagesToArtistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('artists', function(Blueprint $table)
		{
            $table->mediumtext('wiki_image_large')->nullable();
            $table->mediumtext('wiki_image_small')->nullable();
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
			$table->dropColumn('wiki_image_large');
			$table->dropColumn('wiki_image_small');
		});
	}

}
