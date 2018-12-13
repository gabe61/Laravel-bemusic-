<?php namespace App\Services\Providers\Youtube;

use App;
use GuzzleHttp\Exception\BadResponseException;
use Log;
use GuzzleHttp\Client;
use Common\Settings\Settings;
use App\Services\HttpClient;

class YoutubeAudioSearch {

    /**
     * Guzzle http client instance.
     *
     * @var Client
     */
    private $httpClient;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Create new YoutubeSearch instance.
     * @param Settings $settings
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;

        $this->httpClient = new HttpClient([
            'headers' =>  ['Referer' => url('')],
            'base_uri' => 'https://www.googleapis.com/youtube/v3/',
            'exceptions' => true
        ]);
    }

    /**
     * Search using youtube api and given params.
     *
     * @param string $artist
     * @param string $track
     * @return array
     */
    public function search($artist, $track)
    {
        $params = $this->getParams($artist, $track);

        try {
            $response = $this->httpClient->get('search', ['query' => $params]);
        } catch(BadResponseException $e) {
            Log::error($e->getResponse()->getBody()->getContents(), $params);
            $response = [];
        }

        return $this->formatResponse($response);
    }

    private function getParams($artist, $track)
    {
        $append = '';

        //if "live" track is not being requested, append "video" to search
        //query to prefer music videos over lyrics and live videos.
        if ( ! str_contains(strtolower($track), '- live')) {
            $append = 'video';
        }

        $params = [
            'q' => "$artist - $track $append",
            'key' => $this->settings->getRandom('youtube_api_key'),
            'part' => 'snippet',
            'maxResults' => 3,
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'videoCategoryId' => 10, //music
            'topicId' => '/m/04rlf' //music (all genres)
        ];

        $regionCode = $this->settings->get('youtube.region_code');

        if ($regionCode && $regionCode !== 'none') {
            $params['regionCode'] = strtoupper($regionCode);
        }

        return $params;
    }

    /**
     * Format and normalize youtube response for use in our app.
     *
     * @param array $response
     * @return array
     */
    private function formatResponse($response) {

        $formatted = [];

        if ( ! isset($response['items'])) return $formatted;

        return array_map(function($item) {
            return ['title' => $item['snippet']['title'], 'id' => $item['id']['videoId']];
        }, $response['items']);
    }
}