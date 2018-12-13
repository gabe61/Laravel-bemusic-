<?php namespace App\Services\Providers\Local;

use App\Album;
use Illuminate\Database\Eloquent\Collection;

class LocalTopAlbums {

    /**
     * Get top albums using local provider.
     *
     * @return Collection
     */
    public function getTopAlbums() {
        return Album::with('artist', 'tracks')
            ->has('tracks', '>=', 5)
            ->orderBy('views', 'desc')
            ->limit(40)
            ->get();
    }
}