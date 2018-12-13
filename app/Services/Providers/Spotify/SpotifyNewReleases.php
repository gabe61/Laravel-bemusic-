<?php namespace App\Services\Providers\Spotify;

use App\Artist;
use App\Album;
use App\Services\HttpClient;
use App\Services\Artists\ArtistSaver;

class SpotifyNewReleases {

    /**
     * HttpClient instance.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * SpotifyArtist service instance.
     *
     * @var SpotifyArtist
     */
    private $spotifyArtist;

    /**
     * ArtistSaver service instance.
     *
     * @var ArtistSaver
     */
    private $saver;

    /**
     * Create new SpotifyArtist instance.
     *
     * @param ArtistSaver $saver
     * @param SpotifyArtist $spotifyArtist
     * @param SpotifyHttpClient $httpClient
     */
    public function __construct(SpotifyArtist $spotifyArtist, ArtistSaver $saver, SpotifyHttpClient $httpClient)
    {
        $this->saver = $saver;
        $this->httpClient = $httpClient;
        $this->spotifyArtist = $spotifyArtist;

        ini_set('max_execution_time', 0);
    }

    public function getNewReleases()
    {
        $response = $this->httpClient->get('browse/new-releases?country=US&limit=40');

        $albums = $this->spotifyArtist->getAlbums(null, $response['albums']);

        $artists = []; $names = [];

        foreach($albums as $album) {
            $artists[] = [
                'name' => $album['artist']['name'],
                'fully_scraped' => 0
            ];

            $names[] = $album['artist']['name'];
        }

        $existing = Artist::whereIn('name', $names)->get();

        $artistsToFetch = [];

        foreach($artists as $k => $artist) {
            if ($this->inArray($artist['name'], $existing)) {
                unset($artists[$k]);
            } else {
                $artistsToFetch[] = $artist['name'];
            }
        }

        $this->saver->saveOrUpdate($artists, array_flatten($artists), 'artists');

        $new = Artist::whereIn('name', $artistsToFetch)->get();

        $artists = $existing->merge($new);

        $albumNames = [];

        foreach($albums as $k => $album) {
            $model = $artists->filter(function($artist) use($album) { return strtolower($artist->name) == strtolower($album['artist']['name']); })->first();

            $id = $model ? $model->id : false;

            $albums[$k]['artist_id'] = $id;
            $albums[$k]['fully_scraped'] = 1;

            unset($albums[$k]['artist']);

            if ( ! $id) {
                unset($albums[$k]);
                continue;
            }

            $albumNames[] = $album['name'];
        }

        $this->saver->saveAlbums(['albums' => $albums]);

        $albums = Album::with('artist', 'tracks')->whereIn('name', $albumNames)->orderBy('release_date', 'desc')->limit(40)->get();

        return $albums->sortByDesc('artist.spotify_popularity')->values();
    }

    private function inArray($name, $items)
    {
        foreach($items as $item) {
            if (strtolower($name) == strtolower($item->name)) {
                return true;
            }
        }
    }
}