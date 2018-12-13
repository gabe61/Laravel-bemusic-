<?php namespace App\Services\Providers\Local;

class LocalAlbum {


    /**
     * Get album or throw 404 exception if cant find one matching given name.
     *
     * @param string  $artistName
     * @param string  $albumName
     *
     * @return array
     */
    public function getAlbumOrFail($artistName, $albumName) {
       return $this->getAlbum($artistName, $albumName);
    }

    /**
     * Get artists album from spotify.
     *
     * @param string  $artistName
     * @param string  $albumName
     *
     * @return array|void
     */
    public function getAlbum($artistName, $albumName) {
        return null;
    }
}