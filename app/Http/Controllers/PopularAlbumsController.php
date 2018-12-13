<?php namespace App\Http\Controllers;

use App;
use Cache;
use App\Album;
use Carbon\Carbon;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;
use Common\Settings\Settings;

class PopularAlbumsController extends Controller
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * PopularAlbumsController constructor.
     *
     * @param Settings $settings
     * @param ProviderResolver $resolver
     */
    public function __construct(Settings $settings, ProviderResolver $resolver)
    {
        $this->settings = $settings;
        $this->resolver = $resolver;
    }

    /**
     * Get most popular albums.
     *
     * @return mixed
     */
    public function index()
    {
        $this->authorize('index', Album::class);

        return Cache::remember('albums.popular', $this->getCacheTime(), function() {
            $albums = $this->resolver->get('top_albums')->getTopAlbums();
            return ! empty($albums) ? $albums : null;
        });
    }

    /**
     * Get time popular albums should be cached for.
     *
     * @return Carbon
     */
    private function getCacheTime()
    {
        return Carbon::now()->addDays($this->settings->get('cache.homepage_days', 1));
    }
}