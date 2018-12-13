<?php namespace App\Services\Providers\Local;

use App\Track;
use Illuminate\Database\Eloquent\Collection;

class LocalTopTracks {

    /**
     * Get top tracks using local provider.
     *
     * @return Collection
     */
    public function getTopTracks() {
        return Track::with('album.artist')->orderBy('plays', 'desc')->limit(50)->get();
    }
}