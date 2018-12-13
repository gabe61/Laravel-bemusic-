<?php namespace App\Http\Controllers;

use App;
use Cache;
use App\Artist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;

class ArtistRadioController extends Controller {

    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Artist
     */
    private $artist;

    /**
     * Create new RadioController instance.
     *
     * @param Artist $artist
     * @param Request $request
     * @param ProviderResolver $resolver
     */
    public function __construct(ProviderResolver $resolver, Request $request, Artist $artist)
    {
        $this->artist = $artist;
        $this->request = $request;
        $this->resolver = $resolver;
    }

    /**
     * Get recommendations for specified artist radio.
     *
     * @param integer $id
     * @return array
     */
    public function getRecommendations($id)
    {
        $artist = $this->artist->findOrFail($id);

        $this->authorize('show', $artist);

        return Cache::remember("radio.artist.{$artist->id}", Carbon::now()->addDays(2), function() use($artist) {
            return [
                'type' => 'artist',
                'seed' => $artist,
                'recommendations' => $this->resolver->get('radio')->getRecommendations($artist, 'artist'),
            ];
        });
    }
}
