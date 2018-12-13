<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTracksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tracks', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('album_name');
			$table->tinyInteger('number')->unsigned()->index();
			$table->integer('duration')->unsigned()->nullable();
			$table->string('artists')->nullable();
			$table->string('youtube_id')->nullable();
			$table->tinyInteger('spotify_popularity')->unsigned()->nullable()->index();
			$table->integer('album_id')->unsigned()->index();
            $table->boolean('withoutAlbum');

			$table->collation = config('database.connections.mysql.collation');
			$table->charset = config('database.connections.mysql.charset');
		});

        $prefix = DB::getTablePrefix();

        DB::statement('ALTER TABLE `'.$prefix.'tracks` ADD UNIQUE `name_album_unique`(`name`(60), `album_name`(60));');
        DB::statement('ALTER TABLE `'.$prefix.'tracks` ADD INDEX `tracks_artists`(`artists`(60));');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tracks');
	}

}
