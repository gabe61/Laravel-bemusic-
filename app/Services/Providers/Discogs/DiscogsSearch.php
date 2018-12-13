<?php namespace App\Services\Providers\Discogs;

use App\Services\HttpClient;
use App\Services\Search\SearchInterface;

class DiscogsSearch implements SearchInterface {

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
     * Create new SpotifySearch instance.

     * @param HttpClient $client
     */
    public function __construct(HttpClient $client) {
        $this->httpClient = new HttpClient(['base_uri' => 'https://api.discogs.com/database/search']);
        $this->key = config('common.site.discogs.id');
        $this->secret = config('common.site.discogs.secret');
    }

    /**
     * Search using Discogs api and given params.
     *
     * @param string  $q
     * @param int     $limit
     * @param string  $type
     *
     * @return array
     */
    public function search($q, $limit = 10, $type = 'artist,album,track')
    {
        $artists = $this->getArtists($q, $limit);
        $albums  = $this->getAlbums($q, $limit);

        return ['artists' => $artists, 'albums' => $albums, 'tracks' => []];
    }

    private function getArtists($q, $limit)
    {
        $response = $this->httpClient->get("?q=$q&type=artist&secret=$this->secret&key=$this->key");

        return array_map(function($item) {
            return [
                'name' => $item['title'],
                'image_small' => $item['thumb'],
                'discogs_id' => $item['id'],
            ];
        }, array_slice($response['results'], 0, $limit));
    }

    private function getAlbums($q, $limit)
    {
        $response = $this->httpClient->get("?q=$q&type=release&secret=$this->secret&key=$this->key");

        $titles = []; $albums = [];

        foreach($response['results'] as $result) {
            if (count($titles) == $limit) break;

            if ( ! isset($titles[$result['title']])) {
                $titles[$result['title']] = $result['title'];

                $albums[] = [
                    'name'       => explode(' - ', $result['title'])[1],
                    'image'      => $result['thumb'],
                    'discogs_id' => $result['id'],
                    'artist'    => ['name' => explode(' - ', $result['title'])[0]]
                ];
            }
        }

        return $albums;
    }
}