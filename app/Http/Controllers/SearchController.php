<?php namespace App\Http\Controllers;

use App;
use App\Artist;
use App\Track;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Search\UserSearch;
use App\Services\Search\SearchSaver;
use App\Services\Search\PlaylistSearch;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;
use Common\Settings\Settings;

class SearchController extends Controller
{
    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var SearchSaver
     */
    private $saver;

    /**
     * @var UserSearch
     */
    private $userSearch;

    /**
     * @var PlaylistSearch
     */
    private $playlistSearch;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Create new SearchController instance.
     *
     * @param Request $request
     * @param Settings $settings
     * @param SearchSaver $saver
     * @param UserSearch $userSearch
     * @param ProviderResolver $resolver
     * @param PlaylistSearch $playlistSearch
     */
    public function __construct(
        Request $request,
        SearchSaver $saver,
        Settings $settings,
        UserSearch $userSearch,
        ProviderResolver $resolver,
        PlaylistSearch $playlistSearch
    )
    {
        $this->saver = $saver;
        $this->request = $request;
        $this->settings = $settings;
        $this->resolver = $resolver;
        $this->userSearch = $userSearch;
        $this->playlistSearch = $playlistSearch;
    }

    /**
     * Use active search provider to search for
     * songs, albums and artists matching given query.
     *
     * @param string $q
     * @return array
     */
    public function search($q)
    {

        $this->authorize('show', Artist::class);
        $this->authorize('show', Track::class);
        $limit = $this->request->get('limit', 3);
        $results = Cache::remember('search.' . $q . $limit, Carbon::now()->addDays(3), function () use ($q, $limit) {
            $results = $this->resolver->get('search')->search($q, $limit);

            if ($this->resolver->getProviderNameFor('search') !== 'Local') {
                $results = $this->saver->save($results);
            }

            $results['playlists'] = $this->playlistSearch->search($q, $limit);
            $results['users'] = $this->userSearch->search($q, $limit);

            return $results;
        });

        return $this->filterOutBlockedArtists($results);
    }

    /**
     * Search for audio matching given query.
     *
     * @param string $artist
     * @param string $track
     * @return array
     */
    public function searchAudio($artist, $track)
    {
        $this->authorize('show', Track::class);

        return $this->resolver->get('audio_search')->search($artist, $track, 1);
    }

    /**
     * Search local database for matching artists.
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function searchLocalArtists($query)
    {
        $this->authorize('show', Track::class);

        $limit = $this->request->get('limit', 8);

        return Artist::where('name', 'like', "$query%")
            ->orderByPopularity('desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Remove artists that were blocked by admin from search results.
     *
     * @param array $results
     * @return array
     */
    private function filterOutBlockedArtists($results)
    {
        if (($artists = $this->settings->get('artists.blocked'))) {
            $artists = json_decode($artists);

            foreach ($results['artists'] as $k => $artist) {
                if ($this->shouldBeBlocked($artist->name, $artists)) {
                    unset($results['artists'][$k]);
                }
            }

            foreach ($results['albums'] as $k => $album) {
                if (isset($album['artist'])) {
                    if ($this->shouldBeBlocked($album['artist']['name'], $artists)) {
                        unset($results['albums'][$k]);
                    }
                }
            }

            foreach ($results['tracks'] as $k => $track) {
                if (isset($track['album']['artist'])) {
                    if ($this->shouldBeBlocked($track['album']['artist']['name'], $artists)) {
                        unset($results['tracks'][$k]);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Check if given artist should be blocked.
     *
     * @param string $name
     * @param array $toBlock
     * @return boolean
     */
    private function shouldBeBlocked($name, $toBlock)
    {
        foreach ($toBlock as $blockedName) {
            $pattern = '/' . str_replace('*', '.*?', strtolower($blockedName)) . '/i';
            if (preg_match($pattern, $name)) return true;
        }
    }
}
