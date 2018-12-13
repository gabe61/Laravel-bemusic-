<?php namespace App\Services\Providers\Spotify;

use Carbon\Carbon;
use GuzzleHttp\Exception\BadResponseException;
use Log;

class SpotifyArtist {

    /**
     * HttpClient instance.
     *
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * Create new SpotifyArtist instance.
     *
     * @param SpotifyHttpClient $spotifyHttpClient
     */
    public function __construct(SpotifyHttpClient $spotifyHttpClient) {
        $this->httpClient = $spotifyHttpClient;
    }

    /**
     * Get artist or throw 404 exception if cant find one matching given name.
     *
     * @param null|string $name
     *
     * @return array
     */
    public function getArtistOrFail($name = null) {
        $artist = $this->getArtist($name);

        if ( ! $artist) abort(404);

        return $artist;
    }

    /**
     * Get full artist (albums, tracks, similar)
     *
     * @param null|string     $name
     *
     * @return array|false
     */
    public function getArtist($name = null) {
        $artist   = null;
        $artistSecondary = null;

        try {
            $response = $this->httpClient->get("search?type=artist&q=$name&limit=50");
        } catch(BadResponseException $e) {
            Log::error($e->getResponse()->getBody()->getContents(), ['query' => 'name']);
            $response = [];
        }

        //make sure we get exact name match when searching by name
        if (isset($response['artists']['items'][0])) {
            foreach ($response['artists']['items'] as $spotifyArtist) {
            	$normalizedSpotifyName = str_replace([' ', '.'], '', strtolower($spotifyArtist['name']));
            	$normalizedSpecifiedName = str_replace([' ', '.'], '', strtolower($name));

                if ($normalizedSpotifyName === $normalizedSpecifiedName) {
                    $artist = $spotifyArtist; break;
                }

                if (str_contains($normalizedSpotifyName, $normalizedSpecifiedName)) {
                	$artistSecondary = $spotifyArtist;
                }
            }
        }

        if ( ! $artist) $artist = $artistSecondary;

        //if couldn't find artist, bail
        if ( ! $artist) return false;

        $mainInfo = $this->formatArtistInfo($artist);

        //make sure name is the same as we got passed in as sometimes spaces
        //and other things might be in different places on our db and spotify
        $mainInfo['name'] = $name;

        $genres   = $artist['genres'];
        $albums   = $this->getAlbums($artist['id']);
        $similar  = $this->getSimilar($artist['id']);

        return ['mainInfo' => $mainInfo, 'albums' => $albums, 'similar' => $similar, 'genres' => $genres];
    }

    /**
     * Get artists similar to given artist.
     *
     * @param string $spotifyId
     * @return array
     */
    public function getSimilar($spotifyId)
    {
        $response = $this->httpClient->get("artists/{$spotifyId}/related-artists");

        $formatted = [];

        if (isset($response['artists'])) {
            foreach($response['artists'] as $artist) {
                $formatted[] = $this->formatArtistInfo($artist, 0);
            }
        }

        return $formatted;
    }

    /**
     * Get artist albums and their tracks.
     *
     * @param string     $spotifyId
     * @param null|array $albumsToFetch
     * @return array
     */
    public function getAlbums($spotifyId, $albumsToFetch = null)
    {
        $albums = [];

        //get simplified artist albums objects
        if ( ! $albumsToFetch) {
            $response = $this->httpClient->get("artists/{$spotifyId}/albums?offset=0&limit=50&album_type=album,single");
        } else {
            $response = $albumsToFetch;
        }

        $ids = $this->makeAlbumsIdString($response);

        if ( ! $ids) return $albums;

        //get full album objects from spotify
        foreach($ids as $key => $idsString) {

            //limit to 40 albums per artist max
            if ($key === 2) break;

            $response = $this->httpClient->get("albums?ids=$idsString");

            if ( ! isset($response['albums'])) continue;

            $albums = array_merge($albums, $response['albums']);
        }

        return $this->formatAlbums($albums, $albumsToFetch !== null);
    }

    /**
     * Concat ids strings for all albums we want to fetch from spotify.
     *
     * @param mixed $response
     * @return array
     */
    private function makeAlbumsIdString($response) {
        $filtered = [];
        $ids      = '';

        //filter out deluxe albums and same albums that were released in different markets
        if (isset($response['items']) && count($response['items'])) {
            foreach($response['items'] as $album) {
                $name = str_replace(' ', '', strtolower($album['name']));

                if (str_contains($name, '(clean')) continue;


                if (isset($filtered[$name]) && $filtered[$name]['available_markets'] >= $album['available_markets']) continue;

                $filtered[$name] = $album;
            }

            //make multi-dimensional array of 20 spotify album ids as that is the max for albums query
            $chunked = array_chunk(array_map(function($a) { return $a['id']; }, $filtered), 20);

            $ids = array_map(function($a) { return implode(',', $a); }, $chunked);
        }

        return $ids;
    }

    /**
     * Format and normalize spotify album objects for use in our app.
     *
     * @param array $albums
     * @param boolean $addArtist
     * @return array
     */
    public function formatAlbums($albums, $addArtist = false)
    {
        $formatted = array();
        $trackIds  = [];

        if ($albums && ! empty($albums)) {

            //format base album info
            foreach($albums as $album) {

                $formattedAlbum = [
                    'name' => $album['name'],
                    'image'  => $this->getImage($album['images'], 1),
                    'spotify_popularity'  => $album['popularity'],
                    'release_date' => $album['release_date'],
                    'tracks' => $album['tracks'],
                    'fully_scraped' => 1,
                ];

                if ($addArtist) {
                    $formattedAlbum['artist'] = head($album['artists']);
                }

                $formatted[] = $formattedAlbum;

                //make array of all artist tracks spotify ids
                $trackIds = array_merge($trackIds, array_map(function($track) { return $track['id']; }, $album['tracks']['items']));
            }

            //get full info objects for all artist tracks
            $tracks = $this->getTracks($trackIds);

            //attach full track objects to albums
            $formatted = $this->attachTracksToAlbums($formatted, $tracks);
        }

        return $formatted;
    }

    /**
     * Replace simplified track objects with full ones on albums.
     *
     * @param array $albums
     * @param array $tracks
     *
     * @return array
     */
    private function attachTracksToAlbums(array $albums, array $tracks)
    {
        foreach($albums as $key => $album) {
            foreach($album['tracks']['items'] as $k => $track) {
                if (isset($tracks[$track['id']])) {
                    $album['tracks']['items'][$k] = $tracks[$track['id']];
                }
            }

            $album['tracks'] = $album['tracks']['items'];
            $albums[$key] = $album;
        }

        return $albums;
    }

    /**
     * Get full track objects from spotify for all artist tracks.
     *
     * @param array $ids
     * @return array
     */
    private function getTracks(array $ids)
    {
        $chunked = array_chunk($ids, 50);
        $tracks  = [];

        foreach($chunked as $chunk) {
            $chunk = implode(',', $chunk);
            $response = $this->httpClient->get("tracks?ids=$chunk");

            if (isset($response['tracks'])) {
            	$tracks = array_merge($tracks, $response['tracks']);
            }
        }

        return $this->formatTracks($tracks);
    }

    /**
     * Format and normalize track objects array for use in our app.
     *
     * @param array $tracks
     * @return array
     */
    public function formatTracks(array $tracks, $keepArtists = false) {
        $formatted = [];

        //tracks are already formatted
        if (isset($tracks[0]['spotify_popularity'])) return $tracks;

        foreach($tracks as $track) {
            if ( ! isset($track['id'])) continue;

            $formatted[$track['id']] = [
                'duration'   => $track['duration_ms'],
                'name'       => $track['name'],
                'number'     => $track['track_number'],
                'album_name' => $track['album']['name'],
                'artists'    => implode('*|*', array_map(function($a) { return $a['name']; }, $track['artists'])),
                'spotify_popularity' => $track['popularity'],
            ];

            if ($keepArtists) {
                $formatted[$track['id']]['artist'] = $track['artists'][0];
                $formatted[$track['id']]['album'] = $track['album'];
            }
        }

        return $formatted;
    }

    /**
     * Format and normalize spotify response.
     *
     * @param array $data
     * @return array
     */
    private function formatArtistInfo($data, $fullyScraped = 1)
    {
        $formatted = array();

        if ($data) {
            $formatted['spotify_followers'] = $data['followers']['total'] ?: null;
            $formatted['name'] = $data['name'] ?: null;
            $formatted['spotify_popularity'] = $data['popularity'] ?: null;

            //make sure we don't get too small image as it will be streched on front end
            $smallImageIndex = (isset($data['images'][2]) && isset($data['images'][2]['width']) && $data['images'][2]['width'] < 170) ? 1 : 2;
            $formatted['image_small'] = $this->getImage($data['images'], $smallImageIndex);

            $formatted['image_large'] = $this->getImage($data['images']);
            $formatted['fully_scraped'] = $fullyScraped;
            $formatted['updated_at'] = Carbon::now()->toDateTimeString();
        }

        return $formatted;
    }

    /**
     * Get image string from spotify images array if available.
     *
     * @param mixed $images
     * @param int   $index
     * @return mixed
     */
    private function getImage($images, $index = 0)
    {
        if ($images && count($images)) {

            if (isset($images[$index])) {
                return $images[$index]['url'];
            }

            foreach($images as $image) {
                return $image['url'];
            }
        }

        return null;
    }
}