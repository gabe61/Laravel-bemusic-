<?php namespace App\Services\Providers\Local;

use App\Album;
use Illuminate\Database\Eloquent\Collection;

class LocalNewReleases {

    /**
     * Get new album releases using local provider.
     *
     * @return Collection
     */
    public function getNewReleases() {
        return Album::with('artist', 'tracks')
            ->join('artists', 'artists.id', '=', 'albums.artist_id')
            ->orderBy('release_date', 'desc')
            ->limit(40)
            ->select('albums.*')
            ->get();
    }
}