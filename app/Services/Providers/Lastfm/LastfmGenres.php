<?php namespace App\Services\Providers\Lastfm;

use App;
use App\Artist;
use App\Genre;
use Carbon\Carbon;
use Common\Settings\Settings;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use App\Services\HttpClient;
use App\Services\Artists\ArtistSaver;
use Illuminate\Support\Collection;
use App\Services\Providers\Spotify\SpotifyArtist;

class LastfmGenres {

    /**
     * Links of artist placeholder images on last.fm
     *
     * @var array
     */
    private $lastfmPlaceholderImages = [
        'https://lastfm-img2.akamaized.net/i/u/289e0f7b270445e5c550714f606fd8fd.png'
    ];

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyArtist
     */
    private $spotifyArtist;

    /**
     * @var ArtistSaver
     */
    private $saver;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param SpotifyArtist $spotifyArtist
     * @param ArtistSaver $saver
     * @param Settings $settings
     * @param Genre $genre
     * @param Filesystem $fs
     */
    public function __construct(
        SpotifyArtist $spotifyArtist,
        ArtistSaver $saver,
        Settings $settings,
        Genre $genre,
        Filesystem $fs
    )
    {
        $this->httpClient = new HttpClient(['base_uri' => 'http://ws.audioscrobbler.com/2.0/']);
        $this->spotifyArtist = $spotifyArtist;
        $this->saver = $saver;
        $this->settings = $settings;
        $this->apiKey = config('common.site.lastfm.key');

        ini_set('max_execution_time', 0);
        $this->genre = $genre;
        $this->fs = $fs;
    }

    public function getGenres()
    {
        $response = $this->httpClient->get("?method=tag.getTopTags&api_key=$this->apiKey&format=json");

        if ( ! isset($response['toptags'])) {
            sleep(3);
            $response = $this->httpClient->get("?method=tag.getTopTags&api_key=$this->apiKey&format=json");
        }

        $lastfmGenres = collect($response['toptags']['tag']);
        $lowest =  $lastfmGenres->min('count');
        $highest =  $lastfmGenres->max('count');

        // save genres
        $genres = $lastfmGenres->map(function($genreData) use($lowest, $highest) {
            $name = $genreData['name'];

            $data = [
                'name' => $name,
                'popularity' => $this->scalePopularity($genreData['count'], $lowest, $highest),
                'image' => $this->getImage($name),
            ];

            return $this->genre->updateOrCreate(['name' => $name], $data);
        });

        return $genres;
    }

    /**
     * Scale specified last.fm genre popularity to 1-100 range.
     *
     * @param int $value
     * @param int $lowest
     * @param int $highest
     * @return int
     */
    private function scalePopularity($value, $lowest, $highest)
    {
        $min = 1; $max = 100;
        return intval(($max - $min) * ($value - $lowest) / ($highest - $lowest) + $min);
    }

    /**
     * Get default genre image path, if it exists.
     *
     * @param string $name
     * @return null|string
     */
    private function getImage($name)
    {
        $filename = str_slug($name) . '.jpg';
        $path = "client/assets/images/genres/$filename";

        return $this->fs->exists(public_path($path)) ? $path : null;
    }

    /**
     * @param string|array $names
     * @return Genre[]|\Illuminate\Database\Eloquent\Collection
     */
    public function formatGenres($names) {
        if (is_string($names)) {
            $names = explode(',', $names);
        }

        return $this->genre->whereIn('name', $names)->get();
    }

    public function getGenreArtists($genre)
    {
        $genreName = $genre['name'];
        $response  = $this->httpClient->get("?method=tag.gettopartists&tag=$genreName&api_key=$this->apiKey&format=json&limit=50");
        $artists   = $response['topartists']['artist'];
        $names     = [];
        $formatted = [];

        foreach($artists as $artist) {
            if ( ! $this->collectionContainsArtist($artist['name'], $formatted)) {

                $img = ! in_array($artist['image'][4]['#text'], $this->lastfmPlaceholderImages) ? $artist['image'][4]['#text'] : null;

                $formatted[] = [
                    'name' => $artist['name'],
                    'image_small' => $img,
                    'fully_scraped' => 0,
                ];

                $names[] = $artist['name'];
            }
        }

        $existing = Artist::whereIn('name', $names)->get();

        $insert = array_filter($formatted, function($artist) use ($existing) {
            return ! $this->collectionContainsArtist($artist['name'], $existing);
        });

        try {
            Artist::insert($insert);
        } catch(\Exception $e) {
            //
        }

        $artists = Artist::whereIn('name', $names)->get();

        $this->attachGenre($artists, $genre);

        return $artists;
    }

    /**
     * Attach genre to artists in database.
     *
     * @param Collection $artists
     * @param Genre $genre
     */
    private function attachGenre($artists, $genre)
    {
        $pivotInsert = [];

        foreach ($artists as $artist) {
            $pivotInsert[] = ['genre_id' => $genre['id'], 'artist_id' => $artist['id']];
        }

        $this->saver->saveOrUpdate($pivotInsert, array_flatten($pivotInsert), 'genre_artist');
    }

    public function getLocalImagePath($genreName)
    {
        $genreName = str_replace(' ', '-', strtolower(trim($genreName)));

        return 'images/genres/'.$genreName.'.jpg';
    }

    private function collectionContainsArtist($name, $collection) {
        foreach ($collection as $artist) {
            if ($this->normalizeName($name) === $this->normalizeName($artist['name'])) {
                return true;
            }
        }

        return false;
    }

    private function normalizeName($name)
    {
        return trim(Str::ascii(mb_strtolower($name)));
    }
}