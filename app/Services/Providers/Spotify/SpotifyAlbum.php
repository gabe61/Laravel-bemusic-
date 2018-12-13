<?php namespace App\Services\Providers\Spotify;

use App\Services\HttpClient;

class SpotifyAlbum {

    /**
     * HttpClient instance.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Spotify Artist Instance.
     *
     * @var SpotifyArtist
     */
    private $spotifyArtist;

    /**
     * Create new SpotifyArtist instance.
     * @param SpotifyArtist $spotifyArtist
     */
    public function __construct(SpotifyArtist $spotifyArtist) {
        $this->httpClient = \App::make('SpotifyHttpClient');
        $this->spotifyArtist = $spotifyArtist;
    }

    /**
     * Get album or throw 404 exception if cant find one matching given name.
     *
     * @param string  $artistName
     * @param string  $albumName
     *
     * @return array
     */
    public function getAlbumOrFail($artistName, $albumName) {
        $album = $this->getAlbum($artistName, $albumName);

        if ( ! $album) abort(404);

        return $album;
    }

    /**
     * Get artists album from spotify.
     *
     * @param string  $artistName
     * @param string  $albumName
     *
     * @return array
     */
    public function getAlbum($artistName, $albumName) {

        $albumName = trim(explode('(', $albumName)[0]);

        if ( ! $artistName) {
            $response = $this->fetchByAlbumNameOnly(urlencode($albumName));
        } else {
            $response = $this->httpClient->get('search?q=artist:'.$artistName.'%20album:'.str_replace(':', '', $albumName).'&type=album&limit=10');

            //if we couldn't find album with artist and album name, search only by album name
            if ( ! isset($response['albums']['items'][0])) {
                $response = $this->fetchByAlbumNameOnly(urlencode(str_replace(':', '', $albumName)));
            }
        }

        if (isset($response['albums']['items'][0])) {
            $album = false;

            //make sure we get exact name match when searching by name
            foreach ($response['albums']['items'] as $spotifyAlbum) {
                if (str_replace(' ', '', strtolower($spotifyAlbum['name'])) === str_replace(' ', '', strtolower($albumName))) {
                    $album = $spotifyAlbum; break;
                }
            }

            if ( ! $album) $album = $response['albums']['items'][0];

            $id = $album['id'];
            $response = $this->httpClient->get("albums/$id");

            $artist = isset($response['artists'][0]['name']) ? $response['artists'][0]['name'] : null;
            $album = $this->spotifyArtist->formatAlbums([$response])[0];

            if (isset($album['tracks'])) $album['tracks'] = $this->spotifyArtist->formatTracks($album['tracks']);

            return [
                'album'  => $album,
                'artist' => $artist === 'Various Artists' ? null : $artist,
            ];
        }
    }

    private function fetchByAlbumNameOnly($albumName)
    {
        return $this->httpClient->get("search?q=album:$albumName&type=album&limit=10");
    }
}