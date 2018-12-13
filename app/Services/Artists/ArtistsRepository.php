<?php namespace App\Services\Artists;

use App\Album;
use App\Artist;
use App\Genre;
use App\Services\Paginator;
use App\Track;
use Carbon\Carbon;
use Common\Settings\Settings;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Providers\ProviderResolver;

class ArtistsRepository
{
    /**
     * @var Artist
     */
    private $artist;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var ArtistSaver
     */
    private $saver;

    /**
     * @var ArtistAlbumsPaginator
     */
    private $albumsPaginator;

    /**
     * @var ArtistBio
     */
    private $bio;

    /**
     * @var Album
     */
    private $album;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * ArtistsRepository constructor.
     *
     * @param Artist $artist
     * @param Album $album
     * @param Track $track
     * @param Genre $genre
     * @param Settings $settings
     * @param ProviderResolver $resolver
     * @param ArtistSaver $saver
     * @param ArtistAlbumsPaginator $albumsPaginator
     * @param ArtistBio $bio
     */
    public function __construct(
        Artist $artist,
        Album $album,
        Track $track,
        Genre $genre,
        Settings $settings,
        ProviderResolver $resolver,
        ArtistSaver $saver,
        ArtistAlbumsPaginator $albumsPaginator,
        ArtistBio $bio
    )
    {
        $this->bio = $bio;
        $this->saver = $saver;
        $this->artist = $artist;
        $this->settings = $settings;
        $this->resolver = $resolver;
        $this->albumsPaginator = $albumsPaginator;
        $this->album = $album;
        $this->track = $track;
        $this->genre = $genre;
    }

    /**
     * Get artist by id.
     *
     * @param integer $id
     * @param array $params
     * @return array
     */
    public function getById($id, $params = [])
    {
        $artist = $this->artist->findOrFail($id);
        return $this->load($artist, $params);
    }

    /**
     * Get artist by name.
     *
     * @param string $name
     * @param array $params
     * @return array
     */
    public function getByName($name, $params = [])
    {
        $artist = $this->artist->where('name', $name)->first();

        if ( ! $artist && $this->settings->get('artist_provider') !== 'local') {
            $artist = $this->fetchAndStoreArtistFromExternal($name);
            if ( ! $artist) abort(404);
        }

        return $this->load($artist, $params);
    }

    /**
     * Load specified artist.
     *
     * @param Artist $artist
     * @param array $params
     * @return array|Artist
     */
    private function load(Artist $artist, $params = [])
    {
        //return only simplified version of specified artist if requested.
        if (Arr::get($params, 'simplified')) {
            return $artist->load('albums.tracks', 'genres');
        }

        $load = array_filter(explode(',', Arr::get($params, 'with', '')));

        if ($this->needsUpdating($artist)) {
            $newArtist = $this->fetchAndStoreArtistFromExternal($artist->name);
            if ($newArtist) $artist = $newArtist;
        }

        $artist = $artist->load($load);

        $albums = $this->albumsPaginator->paginate($artist->id);

        $response = ['artist' => $artist, 'albums' => $albums];

        if (Arr::get($params, 'top_tracks')) {
            $response['top_tracks'] = $this->getTopTracks($artist->name);
        }

        return $response;
    }

    /**
     * Fetch artist from external service and store it in database.
     *
     * @param string $name
     * @return Artist|null
     */
    private function fetchAndStoreArtistFromExternal($name)
    {
        $artist = null;
        $newArtist = $this->resolver->get('artist')->getArtist($name);

        if ($newArtist) {
            $artist = $this->saver->save($newArtist);
            $artist = $this->bio->get($artist);
            unset($artist['albums']);
        }

        return $artist;
    }

    /**
     * Get 20 most popular artists tracks.
     *
     * @param string $artistName
     * @return Collection
     */
    public function getTopTracks($artistName)
    {
        $tracks = Track::with('album.artist')
            ->where('artists', $artistName)
            ->orWhere('artists', 'like', $artistName.'*|*%')
            ->orderByPopularity('desc')
            ->limit(20)
            ->get();

        return $tracks;
    }

    /**
     * Paginate all artists using specified params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        return (new Paginator($this->artist))->withCount('albums')->paginate($params);
    }

    /**
     * Create a new artist.
     *
     * @param array $params
     * @return Artist
     */
    public function create($params)
    {
        $albums = Arr::pull($params, 'albums', []);
        $genres = Arr::pull($params, 'genres', []);

        $artist = $this->artist->create(Arr::except($params, ['created_at', 'updated_at']));

        foreach ($albums as $album) {
            $tracks = Arr::pull($album, 'tracks', []);

            //create album
            $album['artist_id'] = $artist->id;
            $album['fully_scraped'] = 1;
            $album = $this->album->create($album);

            //set album name, id and artist name on each track
            $tracks = array_map(function($track) use($album, $artist) {
                $track['spotify_popularity'] = Arr::get($track, 'spotify_popularity', 50);
                $track['url'] = Arr::get($track, 'url', null);
                $track['youtube_id'] = Arr::get($track, 'youtube_id', null);
                $track['album_name'] = $album->name;
                $track['album_id'] = $album->id;
                return $track;
            }, $tracks);

            $this->track->insert($tracks);
        }

        //attach genres
        $genreIds = collect($genres)->map(function($genre) {
            return $this->genre->firstOrCreate(['name' => $genre['name']]);
        })->pluck('id');

        $artist->genres()->attach($genreIds);

        return $artist->load('albums.tracks', 'genres');
    }

    /**
     * Update existing artist.
     *
     * @param integer $id
     * @param array $params
     * @return Artist
     */
    public function update($id, $params)
    {
        $artist = $this->artist->findOrFail($id);

        $ids = collect(Arr::pull($params, 'genres', []))->map(function($genre) {
            return $this->genre->firstOrCreate(['name' => $genre['name']]);
        })->pluck('id');

        $artist->genres()->sync($ids);

        $artist->fill(Arr::except($params, ['albums', 'genres', 'created_at', 'updated_at']))->save();

        return $artist->load('albums.tracks', 'genres');
    }

    /**
     * Delete specified artists from database.
     *
     * @param array $ids
     */
    public function delete($ids)
    {
        $albumIds = $this->album->whereIn('artist_id', $ids)->pluck('id');

        $this->artist->whereIn('id', $ids)->delete();
        $this->album->whereIn('id', $albumIds)->delete();
        $this->track->whereIn('album_id', $albumIds)->delete();
    }

    /**
     * Check if specified artist needs to be updated via external site.
     *
     * @param Artist $artist
     * @return bool
     */
    private function needsUpdating(Artist $artist)
    {
        if ($this->settings->get('artist_provider', 'local') === 'local') return false;
        if ( ! $artist->auto_update) return false;

        if ( ! $artist->fully_scraped) return true;

        $updateInterval = $this->settings->get('automation.artist_interval', 7);

        //0 means that artist should never be updated from 3rd party sites
        if ($updateInterval === 0) return false;

        return $artist->updated_at->addDays($updateInterval) <= Carbon::now();
    }
}