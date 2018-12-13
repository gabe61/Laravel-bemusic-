<?php namespace App\Http\Controllers;

use App;
use Cache;
use App\Track;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;

class TrackRadioController extends Controller {

    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Track
     */
    private $track;

    /**
     * Create new RadioController instance.
     *
     * @param ProviderResolver $resolver
     * @param Request $request
     * @param Track $track
     */
    public function __construct(ProviderResolver $resolver, Request $request, Track $track)
    {
        $this->track = $track;
        $this->request = $request;
        $this->resolver = $resolver;
    }

    /**
     * Get recommendations for specified track radio.
     *
     * @param integer $id
     * @return array
     */
    public function getRecommendations($id)
    {
        $track = $this->track->with('album.artist')->findOrFail($id);

        $this->authorize('show', Track::class);

        return Cache::remember("radio.track.{$track->id}", Carbon::now()->addDays(2), function() use($track) {
            return [
                'seed' => $track,
                'type' => 'track',
                'recommendations' => $this->resolver->get('radio')->getRecommendations($track, 'track')
            ];
        });
    }
}
