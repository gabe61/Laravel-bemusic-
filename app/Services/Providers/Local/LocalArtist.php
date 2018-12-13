<?php namespace App\Services\Providers\Local;

class LocalArtist {

    /**
     * Get artist or throw 404 exception if cant find one matching given name.
     *
     * @param null|string $name
     *
     * @return array
     */
    public function getArtistOrFail($name = null) {
        return $this->getArtist($name);
    }

    /**
     * Get full artist (albums, tracks, similar)
     *
     * @param null|string     $name
     *
     * @return array|false
     */
    public function getArtist($name = null)
    {
        return null;
    }
}