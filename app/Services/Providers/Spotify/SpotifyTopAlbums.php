<?php namespace App\Services\Providers\Spotify;

use App\Album;
use Illuminate\Database\Eloquent\Collection;

class SpotifyTopAlbums {

    /**
     * Get top albums using spotify provider.
     *
     * @return Collection
     */
    public function getTopAlbums() {
        return Album::with('artist', 'tracks')
            ->has('tracks', '>=', 5)
            ->orderBy('spotify_popularity', 'desc')
            ->limit(40)
            ->get();
    }
}