<?php namespace App\Services\Providers\Spotify;

use App;
use Log;
use App\Services\HttpClient;
use GuzzleHttp\Exception\ClientException;

class SpotifyHttpClient extends HttpClient {

    /**
     * Spotify api auth token.
     *
     * @var string
     */
	private $token;

    /**
     * Base spotify api url.
     *
     * @var string
     */
	static $baseUrl = 'https://api.spotify.com/v1';

    /**
     * SpotifyHttpClient constructor.
     *
     * @param array $params
     */
	public function __construct($params = [])
    {
		parent::__construct(array_merge(['exceptions' => true]));
	}

    /**
     * Make GET request to spotify api.
     *
     * @param string $uri
     * @param array $options
     * @return array
     */
	public function get($uri, $options = [])
    {
		if ( ! $this->token) $this->getToken();

        return parent::get(self::$baseUrl.'/'.$uri, ['headers' => ['Authorization' => 'Bearer '.$this->token]]);
	}

    /**
     * Get spotify api auth token.
     */
    public function getToken()
    {
        $client = new HttpClient(['exceptions' => true]);

        $auth = [
            'headers' => ['Authorization' => 'Basic '.base64_encode(config('common.site.spotify.id').':'.config('common.site.spotify.secret'))],
            'form_params' => ['grant_type' => 'client_credentials']
        ];

        try {
            $result = $client->post('https://accounts.spotify.com/api/token', $auth);
        } catch (ClientException $e) {
            $result = ['access_token' => null];
            $this->logApiError($e, $auth);
        }

        $this->token = isset($result['access_token']) ? $result['access_token'] : null;
    }

    /**
     * @param ClientException $e
     * @param array $context
     * @return bool
     */
    private function logApiError($e, $context = [])
    {
        return Log::error($e->getResponse()->getBody()->getContents(), $context);
    }
}