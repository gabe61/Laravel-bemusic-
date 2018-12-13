<?php namespace App\Services\Providers\Lastfm;

use App\Services\HttpClient;
use App\Services\Search\SearchInterface;

class LastfmSearch implements SearchInterface {

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var mixed
     */
    private $apiKey;

    /**
     * Create new SpotifySearch instance.
     */
    public function __construct() {
        $this->httpClient = new HttpClient(['base_uri' => 'http://ws.audioscrobbler.com/2.0/']);
        $this->apiKey = config('common.site.lastfm.key');
    }

    /**
     * Search using last.fm api and given params.
     *
     * @param string  $q
     * @param int     $limit
     *
     * @return array
     */
    public function search($q, $limit = 10)
    {
        $queryString = "$q&api_key={$this->apiKey}&format=json&limit=$limit&autocorrect=1";

        $artists = $this->httpClient->get('?method=artist.search&artist='.$queryString);
        $tracks  = $this->httpClient->get('?method=track.search&track='.$queryString);
        $albums  = $this->httpClient->get('?method=album.search&album='.$queryString);

        return $this->formatResponse([
            'artists' => head($artists['results']['artistmatches']),
            'albums'  => head($albums['results']['albummatches']),
            'tracks'  => $this->getFullTracks(head($tracks['results']['trackmatches'])),
        ]);
    }

    /**
     * Format and normalize lastfm response for use in our app.
     *
     * @param array   $response
     *
     * @return array
     */
    private function formatResponse($response) {

        $formatted = ['albums' => [], 'tracks' => [], 'artists' => []];

        $formatted['albums'] = array_map(function($album) {
            return [
                'name' => $album['name'],
                'image' => ! empty($album['image']) ? last($album['image'])['#text'] : null,
                'artist'  => [
                    'name' => $album['artist'],
                    'fully_scraped' => 0
                ]
            ];
        }, $response['albums']);

        $formatted['tracks'] = array_map(function($track) {
            return [
                'name' => $track['name'],
                'image' => ! empty($track['album']['image']) ? head($track['album']['image'])['#text'] : null,
                'lastfm_popularity' => (int) $track['listeners'],
                'duration' => (int) $track['duration'],
                'artists'  => [$track['artist']],
                'number'   => 0,
                'album'    => [
                    'name' => $track['album']['title'],
                    'artist' => $track['album']['artist'],
                ],
            ];
        }, $response['tracks']);

        $formatted['artists'] = array_map(function($artist) {
            return [
                'name' => $artist['name'],
                'image_small' => ! empty($artist['image']) ? $artist['image'][1]['#text'] : null,
                'image_large' => ! empty($artist['image']) ? last($artist['image'])['#text'] : null,
                'lastfm_popularity' => (int) $artist['listeners']
            ];
        }, $response['artists']);

        return $formatted;
    }

    private function getFullTracks($tracks) {
        $ids = array_map(function($t) { return $t['mbid']; }, $tracks);

        return array_map(function($id) {
            return head($this->httpClient->get("?method=track.getInfo&mbid=$id&api_key={$this->apiKey}&format=json"));
        }, $ids);
    }
}