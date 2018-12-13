<?php namespace App\Http\Controllers;

use App;
use App\Album;
use App\Services\Providers\ProviderResolver;
use Cache;
use Carbon\Carbon;
use Common\Core\Controller;
use Common\Settings\Settings;

class NewReleasesController extends Controller
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

        return Cache::remember('albums.latest', $this->getCacheTime(), function() {
            $albums = $this->resolver->get('new_releases')->getNewReleases();
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
        return Carbon::now()->addDays($this->settings->get('cache.homepage_days'));
    }
}