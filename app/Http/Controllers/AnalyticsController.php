<?php namespace App\Http\Controllers;

use Cache;
use App\User;
use App\Album;
use App\Track;
use App\Artist;
use Carbon\Carbon;
use Common\Core\Controller;

class AnalyticsController extends Controller
{

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
     * AnalyticsController Constructor.
     *
     * @param Artist $artist
     * @param Album $album
     * @param Track $track
     * @param User $user
     */
    public function __construct(Artist $artist, Album $album, Track $track, User $user)
    {
        $this->artist = $artist;
        $this->album = $album;
        $this->track = $track;
        $this->user = $user;
    }

    /**
     * Get stats for analytics page.
     *
     * @return array
     */
    public function stats()
    {
        $this->authorize('index', 'ReportPolicy');

        return Cache::remember('analytics.stats', Carbon::now()->addDay(), function () {
            return [
                'tracks' => number_format($this->track->count()),
                'albums' => number_format($this->album->count()),
                'artists' => number_format($this->artist->count()),
                'users' => number_format($this->user->count()),
            ];
        });
    }
}
