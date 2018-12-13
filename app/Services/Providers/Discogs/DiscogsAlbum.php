<?php namespace App\Services\Providers\Discogs;

use App\Services\HttpClient;

class DiscogsAlbum {

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var DiscogsArtist
     */
    private $discogsArtist;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * Create new DiscogsArtist instance.
     *
     * @param DiscogsArtist $discogsArtist
     */
    public function __construct(DiscogsArtist $discogsArtist) {
        $this->httpClient = new HttpClient(['base_uri' => 'https://api.discogs.com/']);
        $this->discogsArtist = $discogsArtist;
        $this->key = config('common.site.discogs.id');
        $this->secret = config('common.site.discogs.secret');
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
     * Get artists album from discogs.
     *
     * @param string  $artistName
     * @param string  $albumName
     *
     * @return array
     */
    public function getAlbum($artistName, $albumName) {
        if ( ! $artistName) {
            $response = $this->fetchByAlbumNameOnly($albumName);
        } else {
            $response = $this->httpClient->get("database/search?q=$albumName&artist=$artistName&key=$this->key&secret=$this->secret&limit=10");

            //if we couldn't find album with artist and album name, search only by album name
            if ( ! isset($response['results'][0])) {
                $response = $this->fetchByAlbumNameOnly($albumName);
            }
        }

        if (isset($response['results'][0])) {
            $album = false;

            //make sure we get exact name match when searching by name
            foreach ($response['results'] as $discogsAlbum) {
                $title = explode(' - ', $discogsAlbum['title'])[1];
                if (str_replace(' ', '', strtolower($title)) === str_replace(' ', '', strtolower($albumName))) {
                    $album = $discogsAlbum; break;
                }
            }

            if ( ! $album) $album = $response['results'][0];

            $response = $this->httpClient->get($album['resource_url']."?key=$this->key&secret=$this->secret");
            
            $artist = isset($response['artists'][0]['name']) ? $response['artists'][0]['name'] : null;

            return [
                'album'  => $this->formatAlbum($response),
                'artist' => $artist === 'Various Artists' ? null : $artist,
            ];
        }
    }

    private function formatAlbum($album)
    {
        $formatted = [
            'name' => $album['title'],
            'release_date' => isset($album['released_formatted']) ? $album['released_formatted']: $album['year'],
            'image' => $this->discogsArtist->getCorrectSizeImage($album['images'], 'smallest'),
            'fully_scraped' => 1,
            'tracks' => [],
        ];

        foreach($album['tracklist'] as $track) {
            if ($track['title']) {
                $formatted['tracks'][] = [
                    'name' => $track['title'],
                    'album_name' => $album['title'],
                    'number' => $track['position'],
                    'duration' => ((float) str_replace(':', '.', $track['duration'])) * 60 * 1000,
                    'artists' => array_map(function($a) { return $a['name']; }, $album['artists']),
                ];
            }
        }

        return $formatted;
    }

    private function fetchByAlbumNameOnly($albumName)
    {
        return $this->httpClient->get("database/search?q=$albumName&type=album&limit=10&key=$this->key&secret=$this->secret");
    }
}