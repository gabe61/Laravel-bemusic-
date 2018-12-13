<?php namespace App\Services\Providers\Local;

use App\Genre;
use App\Services\Providers\Lastfm\LastfmGenres;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Collection;

class LocalGenres {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var LastfmGenres
     */
    private $lastfmGenres;

    /**
     * Create new LocalGenres instance.
     *
     * @param Settings $settings
     * @param Genre $genre
     * @param LastfmGenres $lastfmGenres
     */
    public function __construct(Settings $settings, Genre $genre, LastfmGenres $lastfmGenres)
    {
        $this->genre = $genre;
        $this->settings = $settings;
        $this->lastfmGenres = $lastfmGenres;
    }

    /**
     * Get genres using local provider.
     *
     * @return Collection
     */
    public function getGenres() {
        return $this->genre->limit(50)->orderBy('popularity', 'desc')->get();
    }

    public function getGenreArtists(Genre $genre)
    {
        return null;
    }
}