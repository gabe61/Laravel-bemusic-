<?php namespace App\Services\Providers\Spotify;

use App;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Str;
use App\Services\Search\SearchInterface;
use Log;

class SpotifySearch implements SearchInterface {
    
    /**
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * @param SpotifyHttpClient $spotifyHttpClient
     */
    public function __construct(SpotifyHttpClient $spotifyHttpClient) {
        $this->httpClient = $spotifyHttpClient;
    }

    /**
     * Search using spotify api and given params.
     *
     * @param string  $q
     * @param int     $limit
     * @param string  $type
     *
     * @return array
     */
    public function search($q, $limit = 10, $type = 'artist,album,track')
    {
        if ((bool) preg_match('/[\p{Cyrillic}]/u', $q)) {
            $query = $q;
        }
        else {
            $query = Str::ascii($q);
            if ( ! trim($query)) $query = $q;
            $query = $query.' OR '.$q.'*';
        }

        $query = str_replace('.', '', $query);

        try {
            $response = $this->httpClient->get("search?q=$query&type=$type&limit=$limit");
        } catch(BadResponseException $e) {
            Log::error($e->getResponse()->getBody()->getContents(), ['query' => $query]);
            $response = [];
        }
        
        return $this->formatResponse($response);
    }

    /**
     * Format and normalize spotify response for use in our app.
     *
     * @param array   $response
     *
     * @return array
     */
    private function formatResponse($response) {

        $callback = function($item) {
            $formatted = [
                'spotify_id' => $item['id'],
                'name'       => $item['name'],
                'image_small' =>  null,
                'image_large' =>  null,
            ];

            if (isset($item['images']) && count($item['images'])) {
                $smallImageIndex = (isset($item['images'][2]) && isset($item['images'][2]['width']) && $item['images'][2]['width'] < 170) ? 1 : 2;
                $formatted['image_small'] = $this->getImage($item['images'], $smallImageIndex);
                $formatted['image_large'] = $this->getImage($item['images']);
            }

            if (isset($item['popularity'])) {
                $formatted['spotify_popularity'] = $item['popularity'];
            }

            if (isset($item['duration_ms'])) {
                $formatted['duration'] = $item['duration_ms'];
            }

            if (isset($item['track_number'])) {
                $formatted['number'] = $item['track_number'];
            }

            if (isset($item['genres'])) {
                $formatted['genres'] = implode('|', $item['genres']);
            }

            if (isset($item['artists']) && count($item['artists'])) {
                $formatted['artists'] = $item['artists'];
            }

            if (isset($item['album']) && count($item['album'])) {
                $formatted['album'] = $item['album'];

                if ( ! isset($formatted['image'])) {
                    if (isset($item['album']['images'][2]['url'])) {
                        $formatted['image'] = $item['album']['images'][2]['url'];
                    } else {
                        $formatted['image'] = head($item['album']['images']);
                    }

                }
            }

            return $formatted;
        };

        $formatted = ['albums' => [], 'tracks' => [], 'artists' => []];

        if ( ! isset($response['error'])) {
            $formatted['albums']  = $this->getAlbums(isset($response['albums']['items']) ? $response['albums']['items'] : []);
            $formatted['tracks']  = array_map($callback, isset($response['tracks']['items']) ? $response['tracks']['items'] : []);
            $formatted['artists'] = array_map($callback, isset($response['artists']['items']) ? $response['artists']['items'] : []);
        }

        return $formatted;
    }

    /**
     * Fetch full album objects from spotify and format them.
     *
     * @param array $albums
     * @return array
     */
    private function getAlbums($albums)
    {
        $formatted = [];

        if (empty($albums)) return $formatted;

        foreach($albums as $album) {
            $artist = [
                'name'           => $album['artists'][0]['name'],
                'spotify_id'    => $album['artists'][0]['id'],
                'fully_scraped' => 0
            ];

            $formatted[] = [
                'name'       => $album['name'],
                'popularity' => null,
                'artist'     => $artist,
                'image'      =>  isset($album['images'][1]['url']) ? $album['images'][1]['url'] : null,
                'fully_scraped' => 0
            ];
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