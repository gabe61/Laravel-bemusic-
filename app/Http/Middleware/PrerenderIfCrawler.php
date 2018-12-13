<?php namespace App\Http\Middleware;

use App;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\GenreArtistsController;
use App\Http\Controllers\NewReleasesController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PopularAlbumsController;
use App\Http\Controllers\GenresController;
use App\Http\Controllers\TopTracksController;
use App\Http\Controllers\TrackController;
use Closure;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\PrerenderUtils;
use App\Http\Controllers\SearchController;
use Common\Auth\Controllers\UsersController;

class PrerenderIfCrawler
{
    /**
     * @var PrerenderUtils
     */
    private $utils;

    /**
     * List of crawler user agents.
     *
     * @var array
     */
    private $crawlerUserAgents = [
        'googlebot',
        'yahoo',
        'bingbot',
        'baiduspider',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'Google-StructuredDataTestingTool',
        'Google-Structured-Data-Testing-Tool',
        'slackbot',
        'YandexBot'
    ];

    /**
     * PrerenderIfCrawler constructor.
     *
     * @param PrerenderUtils $utils
     */
    public function __construct(PrerenderUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Prerender request if it's requested by a crawler.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $type
     * @return Request|View
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if ($this->shouldPrerender($request)) {
            switch ($type) {
                case 'artist':
                    return $this->prerenderArtist($request);
                case 'album':
                    return $this->prerenderAlbum($request);
                case 'track':
                    return $this->prerenderTrack($request);
                case 'playlist':
                    return $this->prerenderPlaylist($request);
                case 'user':
                    return $this->prerenderUser($request);
                case 'search':
                    return $this->prerenderSearchPage($request);
                case 'genre':
                    return $this->prerenderGenrePage($request);
                case 'new-releases':
                    return $this->prerenderIndexPage(NewReleasesController::class, $type);
                case 'popular-genres':
                    return $this->prerenderIndexPage(GenresController::class, $type);
                case 'homepage':
                    return $this->prerenderHomepage($request);
                case 'popular-albums':
                    return $this->prerenderIndexPage(PopularAlbumsController::class, $type);
                case 'top-50':
                    return $this->prerenderIndexPage(TopTracksController::class, $type);
            }
        }

        return $next($request);
    }

    /**
     * Check if request should be prerendered.
     *
     * @param Request $request
     * @return bool
     */
    private function shouldPrerender(Request $request)
    {
        $userAgent = strtolower($request->server->get('HTTP_USER_AGENT'));
        $bufferAgent = $request->server->get('X-BUFFERBOT');

        $shouldPrerender = false;

        if ( ! $userAgent) return false;
        if ( ! $request->isMethod('GET')) return false;

        // prerender if _escaped_fragment_ is in the query string
        if ($request->query->has('_escaped_fragment_')) $shouldPrerender = true;

        // prerender if a crawler is detected
        foreach ($this->crawlerUserAgents as $crawlerUserAgent) {
            if (str_contains($userAgent, strtolower($crawlerUserAgent))) {
                $shouldPrerender = true;
            }
        }

        if ($bufferAgent) $shouldPrerender = true;

        return $shouldPrerender;
    }

    /**
     * Prerender artist page for crawlers.
     *
     * @param Request $request
     * @return Request|view
     */
    private function prerenderArtist(Request $request)
    {
        if ( ! $request->route('id')) $request->merge(['by_name' => true]);
        $nameOrId = $request->route('id') ? $request->route('id') : urldecode($request->route('name'));

        $data = App::make(ArtistController::class)->show($nameOrId);
        $payload = ['data' => $data, 'utils' => $this->utils];

        return response(view('prerender.artist')->with($payload));
    }

    /**
     * Prerender album for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderAlbum(Request $request)
    {
        $album = App::make(AlbumController::class)->show($request->route('albumId'));
        $payload = ['album' => $album, 'utils' => $this->utils];
        return response(view('prerender.album')->with($payload));
    }

    /**
     * Prerender track for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderTrack(Request $request)
    {
        $track = App::make(TrackController::class)->show($request->route('id'));
        $payload = ['track' => $track, 'utils' => $this->utils];
        return response(view('prerender.track')->with($payload));
    }

    /**
     * Prerender playlist for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderPlaylist(Request $request)
    {
        $data = App::make(PlaylistController::class)->show($request->route('id'));
        $payload = ['data' => $data, 'utils' => $this->utils];
        return response(view('prerender.playlist')->with($payload));
    }

    /**
     * Prerender user page for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderUser(Request $request)
    {
        $user = App::make(UsersController::class)->show($request->route('id'));
        $payload = ['user' => $user, 'utils' => $this->utils];
        return response(view('prerender.user')->with($payload));
    }

    /**
     * Prerender search page for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderSearchPage(Request $request)
    {
        $request->merge(['limit' => 20]);
        $query = $name = urldecode($request->route('query'));
        $results = App::make(SearchController::class)->search($query);
        $payload = ['results' => $results, 'query' => $request->route('query'), 'utils' => $this->utils];
        return response(view('prerender.search')->with($payload));
    }

    /**
     * Prerender genre page for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderGenrePage(Request $request)
    {
        $name = urldecode($request->route('name'));
        $data = App::make(GenreArtistsController::class)->index($name);
        $payload = ['data' => $data, 'name' => $name, 'utils' => $this->utils];
        return response(view('prerender.genre')->with($payload));
    }

    /**
     * Prerender homepage for crawlers.
     *
     * @param Request $request
     * @return Request|View
     */
    private function prerenderHomepage(Request $request)
    {
        $name = urldecode($request->route('name'));
        $data = App::make(GenresController::class)->index();
        $payload = ['data' => $data, 'name' => $name, 'utils' => $this->utils];
        return response(view('prerender.homepage')->with($payload));
    }

    /**
     * Prerender genre page for crawlers.
     *
     * @param string $controller
     * @param string $type
     * @return Request|View
     */
    private function prerenderIndexPage($controller, $type)
    {
        $data = App::make($controller)->index();
        $payload = ['data' => $data, 'utils' => $this->utils];
        return response(view("prerender.$type")->with($payload));
    }
}