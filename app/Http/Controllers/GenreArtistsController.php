<?php namespace App\Http\Controllers;

use App;
use Cache;
use App\Genre;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;

class GenreArtistsController extends Controller {

    /**
     * Data provider resolver instance.
     *
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var Request
     */
    private $request;

    /**
     * GenreController constructor.
     *
     * @param ProviderResolver $resolver
     * @param Genre $genre
     * @param Request $request
     */
    public function __construct(ProviderResolver $resolver, Genre $genre, Request $request)
	{
        $this->resolver = $resolver;
        $this->genre = $genre;
        $this->request = $request;
    }

	/**
	 * Paginate artists for specified genre.
	 *
	 * @param string $name
	 * @return array
	 */
	public function index($name)
	{
        $this->authorize('index', Genre::class);

	    $genre = $this->genre->where('name', $name)->firstOrFail();

        Cache::remember("genres.$name.artists", Carbon::now()->addDays(3), function() use ($genre) {
            return $this->resolver->get('genres')->getGenreArtists($genre);
        });

        $artists = $genre->artists()->newQuery();

        if ($query = $this->request->get('query')) {
            $artists->where('name', 'like', $query.'%');
        }

        $artists = $artists->paginate(20)->toArray();

        return ['genre' => $genre, 'artistsPagination' => $artists];
	}
}
