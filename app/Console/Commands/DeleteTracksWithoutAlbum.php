<?php

namespace App\Console\Commands;

use DB;
use App\Album;
use App\Playlist;
use App\Track;
use Illuminate\Console\Command;

class DeleteTracksWithoutAlbum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:delete_tracks_without_album';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all tracks that are not attached to any album.';

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
        $ids = $this->track->has('album', '<', 1)->pluck('id');

        $this->track->whereIn('id', $ids)->delete();
        DB::table('playlist_track')->whereIn('track_id', $ids)->delete();
        DB::table('track_user')->whereIn('track_id', $ids)->delete();

        $this->info("Deleted {$ids->count()} tracks");
    }
}
