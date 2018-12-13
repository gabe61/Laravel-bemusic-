<?php namespace App\Services\Artists;

use App;
use App\Artist;
use App\Services\HttpClient;
use Common\Settings\Settings;

class ArtistBio {

    /**
     * @var HttpClient
     */
	private $httpClient;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * ArtistBio constructor.
     *
     * @param HttpClient $httpClient
     * @param Settings $settings
     */
    public function __construct(HttpClient $httpClient, Settings $settings)
    {
        $this->httpClient = $httpClient;
        $this->settings = $settings;
    }

    /**
     * Get artist biography and images.
     *
     * @param Artist $artist
     * @return Artist
     */
	public function get(Artist $artist)
	{
        $provider = $this->settings->get('artist_bio_provider', 'wikipedia');

        if ($provider === 'wikipedia') {
            $bio = $this->getFromWikipedia($artist->name);
        } else if ($provider = 'echonest') {
            $bio = $this->getFromEchonest($artist->name);
        }

        if (isset($bio)) {
            $artist->bio = json_encode($bio);
            $artist->save();
        }

        return $artist;
	}

    /**
     * Fetch artist bio and images from Wikipedia.
     *
     * @param string $name
     * @return array
     */
    private function getFromWikipedia($name)
    {
        $lang = $this->settings->get('wikipedia_language', 'en');
        $url  = $this->makeWikipediaApiUrl($name, $lang);

        $response = $this->httpClient->get($url);
        if ( ! isset($response['query']['pages'])) return [];

        $response = $response['query']['pages'];

        //if we didn't find bio and language is not english, fetch bio in english
        if ( ! isset($response[max(array_keys($response))]['extract']) && $lang !== 'en') {
            $response = $this->httpClient->get($this->makeWikipediaApiUrl($name, 'en'));
            $response = $response['query']['pages'];
        }

        $bioResponse = $this->extractBioFromWikipediaResponse($response);

        $response = $this->httpClient->get($this->makeWikipediaApiUrl($name, 'en', 'images', $bioResponse['type']));

        if ( ! isset($response['query']['pages'])) {
            $filtered = []; $images = [];
        } else {
            $urls = array_map(function($item) {
                return ['url' => isset($item['imageinfo']) ? head($item['imageinfo'])['url'] : null];
            }, $response['query']['pages']);

            $images = array_filter($urls, function($image) {
                return ! str_contains($image['url'], '.svg');
            });

            $filtered = array_filter($images, function($image) use($name) {
                return str_contains($image['url'], $name);
            });
        }

        return ['bio' => $bioResponse['bio'], 'images' => array_slice(count($filtered) > 3 ? $filtered : $images, 0, 4)];
    }

    /**
     * Extract artist biography from the correct wikipedia page.
     *
     * @param $response
     * @return array
     */
    private function extractBioFromWikipediaResponse($response)
    {
        if ( ! is_array($response) || empty($response)) return '';

        foreach($response as $item) {
            if (str_contains($item['title'], 'singer') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'singer'];
            if (str_contains($item['title'], 'band') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'band'];
            if (str_contains($item['title'], 'rapper') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'rapper'];
        }

        $length  = 0;
        $longest = '';

        foreach($response as $item) {
            if (isset($item['extract']) && $item['extract']) {
                if (strlen($item['extract']) > $length) {
                    $length = strlen($item['extract']); $longest = $item['extract'];
                }
            }
        }

        return ['bio' => $longest, 'type' => false];
    }

    /**
     * Make url for wikipedia artist api page.
     *
     * @param string  $name
     * @param string  $lang
     * @param boolean $getImages
     * @param string|boolean $type
     *
     * @return string
     */
    private function makeWikipediaApiUrl($name, $lang = 'en', $getImages = false, $type = false)
    {
        $name = str_replace(' ', '_', ucwords(strtolower($name)));

        if ($type) {
            $titles = $name."_($type)";
        } else {
            $titles = "$name|".$name."_(rapper)|".$name."_(band)|".$name."_(singer)";
        }

        if ($getImages) {
            return "https://en.wikipedia.org/w/api.php?action=query&titles=$titles&generator=images&gimlimit=30&prop=imageinfo&iiprop=url|dimensions|mime&format=json&redirects=1";
        }

        return "https://$lang.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=$titles&redirects=1&exlimit=4";
    }

    /**
     * Fetch artist bio and images from EchoNest API.
     *
     * @param string $name
     * @return array
     */
    private function getFromEchonest($name)
    {
        $response = $this->httpClient->get('http://developer.echonest.com/api/v4/artist/profile?bucket=biographies&bucket=images', ['query' => [
            'api_key' => $this->settings->get('echonest_api_key'),
            'name'    => $name,
            'format'  => 'json',
        ]]);

        if (isset($response['response']['artist']['biographies'])) {
            foreach($response['response']['artist']['biographies'] as $bio) {
                if ( ! isset($bio['truncated']) ||  ! $bio['truncated']) {
                    $biography = $bio['text']; break;
                }
            }
        }

        return [
            'images' => isset($response['response']['artist']['images']) ? array_slice($response['response']['artist']['images'], 0, 10) : [],
            'bio'    => isset($biography) ? $biography : ''
        ];
    }

}
