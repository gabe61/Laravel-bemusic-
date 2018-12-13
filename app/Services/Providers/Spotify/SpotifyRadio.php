<?php namespace App\Services\Providers\Spotify;

use App\Artist;
use App\Track;

class SpotifyRadio {

    /**
     * HttpClient instance.
     *
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * Spotify Search Instance.
     *
     * @var SpotifySearch
     */
    private $spotifySearch;

    /**
     * @var SpotifyTopTracks
     */
    private $spotifyTopTracks;

    /**
     * Create new SpotifyArtist instance.
     *
     * @param SpotifySearch $spotifySearch
     * @param SpotifyHttpClient $httpClient
     * @param SpotifyTopTracks $spotifyTopTracks
     */
    public function __construct(SpotifySearch $spotifySearch, SpotifyHttpClient $httpClient, SpotifyTopTracks $spotifyTopTracks) {
        $this->httpClient = $httpClient;
        $this->spotifySearch = $spotifySearch;
        $this->spotifyTopTracks = $spotifyTopTracks;
    }

    /**
     * Get recommendations for specified artist radio from spotify API.
     *
     * @param Artist|Track $item
     * @param string $type
     * @return array
     */
    public function getRecommendations($item, $type)
    {
        if ( ! $spotifyId = $this->getSpotifyId($item, $type)) return [];

        $response = $this->httpClient->get("recommendations?seed_{$type}s=$spotifyId&min_popularity=30&limit=100");

        if ( ! isset($response['tracks'])) return [];

        return $this->spotifyTopTracks->saveAndLoad($response['tracks'], 100);
    }

    /**
     * Get seed information from spotify API.
     *
     * @param Artist|Track $item
     * @param string $type
     * @return array
     */
    private function getSpotifyId($item, $type)
    {
        if ($type === 'artist') {
            $response = $this->spotifySearch->search($item->name, 1, 'artist');
            return isset($response['artists'][0]['spotify_id']) ? $response['artists'][0]['spotify_id'] : null;
        } else {
            $response = $this->spotifySearch->search("{$item->name}+artist:{$item->album->artist->name}", 1, 'track');
            return isset($response['tracks'][0]['spotify_id']) ? $response['tracks'][0]['spotify_id'] : null;
        }
    }
}