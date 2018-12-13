<?php

namespace App\Console\Commands;

use DB;
use App\Album;
use App\Playlist;
use App\Track;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class DeleteAlbumsWithoutArtists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:delete_albums_without_artists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all albums what are not attached to any artist.';

    /**
     * @var Album
     */
    private $album;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * Create a new command instance.
     *
     * @param Album $album
     * @param Track $track
     * @param Playlist $playlist
     */
    public function __construct(Album $album, Track $track, Playlist $playlist)
    {
        parent::__construct();

        $this->album = $album;
        $this->track = $track;
        $this->playlist = $playlist;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $chunkSize = 100;

        $count = $this->album->whereNull('artist_id')->orWhere('artist_id', 0)->orderBy('id')->count();

        $bar = $this->output->createProgressBar($count / $chunkSize);

        $this->album->whereNull('artist_id')->orWhere('artist_id', 0)->orderBy('id')->chunk($chunkSize, function(Collection $albums) use($bar) {
            $ids = $albums->pluck('id');

            $this->album->whereIn('id', $ids)->delete();
            $trackIds = $this->track->whereIn('album_id', $ids)->pluck('id');
            $this->track->whereIn('id', $trackIds)->delete();
            DB::table('playlist_track')->whereIn('track_id', $trackIds)->delete();
            DB::table('track_user')->whereIn('track_id', $trackIds)->delete();

            $bar->advance();
        });

        $bar->finish();
    }
}
