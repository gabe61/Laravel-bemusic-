<?php

use Illuminate\Database\Migrations\Migration;

class AddTempIdToAlbums extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('albums', function($table)
		{
    		$table->string('temp_id', 8)->index()->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('albums', function($table)
		{
		    $table->dropColumn('temp_id');
		});
	}

}