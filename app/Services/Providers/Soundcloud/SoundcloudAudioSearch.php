<?php namespace App\Services\Providers\Soundcloud;

use App;
use Common\Settings\Settings;
use GuzzleHttp\Client;
use App\Services\HttpClient;

class SoundcloudAudioSearch {

    /**
     * Guzzle http client instance.
     *
     * @var Client
     */
    private $httpClient;

    /**
     * Setting service instance.
     *
     * @var Settings
     */
    private $settings;

    /**
     * SoundCloud API Key
     *
     * @var string
     */
    private $key;

    /**
     * Create new YoutubeSearch instance.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings) {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://api.soundcloud.com/',
            'exceptions' => true,
        ]);

        $this->settings = $settings;
        $this->key = config('common.site.soundcloud.key');
    }

    /**
     * Search using soundcloud api and given params.
     *
     * @param string $artistName
     * @param string $trackName
     * @param int    $limit
     * @param string $type
     *
     * @return array
     */
    public function search($artistName, $trackName, $limit = 10, $type = 'video')
    {
        $trackName = $this->formatTrackName($trackName);

        $response = $this->httpClient->get("tracks?order=hotness&client_id={$this->key}&q={$artistName}-{$trackName}&limit=100");

        return $this->formatResponse($response, $artistName, $trackName);
    }

    /**
     * Format and normalize youtube response for use in our app.
     *
     * @param array $response
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    private function formatResponse($response, $artistName, $trackName) {

        $formatted = [];

        if (empty($response)) return $formatted;

        //get best matches from whole result set
        $filtered = $this->getBestMatches($response, $artistName, $trackName);

        //if we've got no matches back, then include unpopular results as well
        if (empty($filtered)) $filtered = $this->getBestMatches($response, $artistName, $trackName, false);

        //find the closest match in result title
        $match = ! empty($filtered) ? $this->getClosestMatch($filtered, $artistName, $trackName) : head($response);
            
        $formatted['name'] = $match['title'];
        $formatted['id']   = $match['uri'];

        return [$formatted];
    }

    /**
     * Filter out covers, remixes, unpopular tracks etc
     *
     * @param array $results
     * @param string $artistName
     * @param string $trackName
     * @param bool|true $filterOutUnpopular
     * @return array
     */
    private function getBestMatches($results, $artistName, $trackName, $filterOutUnpopular = true) {
        $badWords = ['cover', 'remix', 'acoustic', 'piano', 'nightcore'];

        return array_filter($results, function($track) use($artistName, $trackName, $filterOutUnpopular, $badWords) {
            $title = strtolower($track['title']);

            $seemsLikeOriginal = ! str_contains($title, $badWords) && ! str_contains(strtolower($track['tag_list']), $badWords);
            $seemsLikeMatch    = str_contains($title, strtolower($artistName)) && str_contains($title, strtolower($trackName));
            $popularEnough     = $filterOutUnpopular ? ($track['playback_count'] > 1000 && $track['likes_count'] > 100) : true;
            $isNotDemo         = $track['duration'] !== 90757; //1:30
            $isNotLive         = str_contains(strtolower($trackName),'live') ? true : ! str_contains($title, 'live') && $track['track_type'] !== 'live';
            return $seemsLikeMatch && $seemsLikeOriginal && $popularEnough && $isNotDemo && $isNotLive;
        });
    }

    /**
     * Find result which title matches artist name and track name closest.
     *
     * @param array $items
     * @param string $artist
     * @param string $track
     * @return array
     */
    private function getClosestMatch($items, $artist, $track) {
        $shortest = -1;
        $closest  = head($items);

        foreach ($items as $item) {
            $lev = levenshtein($item['title'], "$artist - $track");

            if ($lev == 0) {
                return $item;
            }

            if ($lev <= $shortest || $shortest < 0) {
                $closest  = $item;
                $shortest = $lev;
            }
        }

        return $closest;
    }

    /**
     * Remove any extra words and characters from track name for more accurate search.
     *
     * @param string $trackName
     * @return string
     */
    private function formatTrackName($trackName) {
        $trackName = str_replace(' - Without Skits', '', $trackName);

        if (str_contains($trackName, ['(', ')'])) {
            $trackName = trim(explode('(', $trackName)[0]);
        }

        if (str_contains($trackName, '-')) {
            $trackName = trim(explode('-', $trackName)[0]);
        }

        return $trackName;
    }
}