<?php

namespace App\Console\Commands;

use App\Album;
use App\Artist;
use App\Track;
use App\User;
use Cache;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CachePaginationCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagination:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache table total item counts for pagination.';

    /**
     * @var Artist
     */
    private $artist;

    /**
     * @var Album
     */
    private $album;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var User
     */
    private $user;

    /**
     * CachePaginationCounts Constructor.
     *
     * @param Artist $artist
     * @param Album $album
     * @param Track $track
     * @param User $user
     */
    public function __construct(Artist $artist, Album $album, Track $track, User $user)
    {
        parent::__construct();

        $this->artist = $artist;
        $this->album = $album;
        $this->track = $track;
        $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $artistCount = $this->artist->count();
        $albumCount = $this->album->count();
        $trackCount =  $this->track->count();
        $userCount = $this->user->count();

        Cache::put("pagination.artist_count", $artistCount, Carbon::now()->addDay());
        Cache::put("pagination.album_count", $albumCount, Carbon::now()->addDay());
        Cache::put("pagination.track_count", $trackCount, Carbon::now()->addDay());
        Cache::put("pagination.users_count", $userCount, Carbon::now()->addDay());

        Cache::put('analytics.stats', [
                'tracks'  => number_format($trackCount),
                'albums'  => number_format($albumCount),
                'artists' => number_format($artistCount),
                'users'   => number_format($userCount),
        ], Carbon::now()->addDay());

        $this->info('Cached table counts successfully.');
    }
}
