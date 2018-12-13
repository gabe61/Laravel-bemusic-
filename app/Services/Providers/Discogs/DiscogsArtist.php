<?php namespace App\Services\Providers\Discogs;

use App\Services\HttpClient;

class DiscogsArtist {

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;


    /**
     * Create new SpotifyArtist instance.
     */
    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.discogs.com/',
        ]);

        $this->key = config('common.site.discogs.id');
        $this->secret = config('common.site.discogs.secret');
    }

    /**
     * Get artist or throw 404 exception if cant find one matching given name.
     *
     * @param null|string $name
     *
     * @return array
     */
    public function getArtistOrFail($name = null)
    {
        $artist = $this->getArtist($name);

        if ( ! $artist) abort(404);

        return $artist;
    }

    /**
     * Get artist, his albums and those albums tracks.
     *
     * @param string $identifier
     * @return array|false
     */
    public function getArtist($identifier = null)
    {
        if ( ! $identifier) return [];

        $identifier = $this->getArtistDiscogsId($identifier);

        $mainData = $this->httpClient->get("artists/$identifier", ['query' => [
            'key'    => $this->key,
            'secret' => $this->secret
        ]]);

        $albums = $this->httpClient->get("artists/$identifier/releases", ['query' => [
            'key'      => $this->key,
            'secret'   => $this->secret,
            'per_page' => 100,
            'sort'     => 'year',
            'sort_order' => 'desc',
        ]]);

        $id = false;
        foreach($albums['releases'] as $release) {
            if ($release['artist'] === $mainData['name'] && $release['type'] === 'master') {
                $id = $release['id'];
            }
        }

        $fullAlbum = $this->httpClient->get("masters/$id", ['query' => [
            'key'      => $this->key,
            'secret'   => $this->secret
        ]]);

        return $this->formatArtistData($mainData, $albums, $fullAlbum);
    }

    private function formatArtistData($mainData, $albums, $fullAlbum)
    {
        $formatted = [
            'mainInfo' => [
                'bio'  => $mainData['profile'],
                'name' => $mainData['name'],
                'image_small' => $this->getCorrectSizeImage(isset($mainData['images']) ? $mainData['images'] : [], 'smallest'),
                'image_large' => $this->getCorrectSizeImage(isset($mainData['images']) ? $mainData['images'] : [], 'largest'),
                'fully_scraped' => 1
            ],
            'albums'  => [],
            'genres'  => [],
            'similar' => [],
        ];

        foreach($albums['releases'] as $album) {
            if ($album['artist'] !== $formatted['mainInfo']['name']) continue;

            $formatted['albums'][] = [
                'name'  => $album['title'],
                'image' => $album['thumb'],
                'release_date' => isset($album['year']) ? $album['year'] : null
            ];
        }

        if ($fullAlbum) {
           if (isset($fullAlbum['genres'])) {
               $formatted['genres'] = array_map(function($genreName) {
                   return $genreName;
               }, $fullAlbum['genres']);
           }

           if (isset($fullAlbum['tracklist'])) {
               $formatted['albums'][0]['tracks'] = array_map(function($track) use($fullAlbum) {
                   return [
                       'name' => $track['title'],
                       'album_name' => $fullAlbum['title'],
                       'number' => $track['position'],
                       'duration' => ((float) str_replace(':', '.', $track['duration'])) * 60 * 1000,
                       'artists' => array_map(function($a) { return $a['name']; }, $fullAlbum['artists']),
                   ];
               }, $fullAlbum['tracklist']);
           }
        }

        return $formatted;
    }

    /**
     * Normalize Discogs images array.
     *
     * @param $images
     * @param string $type
     * @return array
     */
    public function getCorrectSizeImage($images, $type)
    {
        if (empty($images)) return null;

        $match = $images[0];

        foreach($images as $image) {
            if ($type === 'smallest') {
                if ($image['width'] < $match['width']) {
                    $match = $image;
                }
            } else {
                if ($image['width'] > $match['width']) {
                    $match = $image;
                }
            }
        }

        return $match['uri'];
    }

    /**
     * Get specified artists Discogs ID.
     *
     * @param string $artistName
     * @return string|bool
     */
    private function getArtistDiscogsId($artistName)
    {
        $response = $this->httpClient->get('database/search', ['query' => [
            'q'      => str_replace('/', '+', $artistName),
            'type'   => 'artist',
            'key'    => $this->key,
            'secret' => $this->secret
        ]]);

        foreach ($response['results'] as $result) {
            if (strtolower($result['title']) == strtolower($artistName)) {
                return $result['id'];
            }
        }

        //if we couldn't find a match by now try to slugify both names for less strict comparison
        foreach ($response['results'] as $result) {
            if (str_slug($result['title']) == str_slug($artistName)) {
                return $result['id'];
            }
        }

        return false;
    }
}