<?php namespace App\Http\Controllers;

use App;
use App\Genre;
use Cache;
use Carbon\Carbon;
use Common\Database\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\Providers\ProviderResolver;
use Common\Core\Controller;
use Common\Settings\Settings;
use Illuminate\Validation\Rule;

class GenresController extends Controller
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
     * @var Genre
     */
    private $genre;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Settings $settings
     * @param ProviderResolver $resolver
     * @param Genre $genre
     * @param Request $request
     */
    public function __construct(
        Settings $settings,
        ProviderResolver $resolver,
        Genre $genre,
        Request $request
    )
    {
        $this->settings = $settings;
        $this->resolver = $resolver;
        $this->genre = $genre;
        $this->request = $request;
    }

    /**
     * @return Collection
     */
    public function popular()
    {
        $this->authorize('index', Genre::class);

        $cacheTime = Carbon::now()->addDays($this->settings->get('cache.homepage_days'));;

        return Cache::remember('genres.popular', $cacheTime, function() {
            $genres = $this->resolver->get('genres')->getGenres();
            return ! empty($genres) ? $genres : null;
        });
    }

    /**
     * @return Collection
     */
    public function index()
    {
        $this->authorize('index', Genre::class);

        $params = $this->request->all();
        if ( ! isset($params['order_by'])) {
            $params['order_by'] = 'popularity';
        }

        return (new Paginator($this->genre))->paginate($params);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Genre::class);

        $this->validate($this->request, [
            'name' => 'required|unique:genres',
            'image' => 'string',
            'popularity' => 'integer|min:1|max:100'
        ]);

        $newGenre = $this->genre->create([
            'name' => $this->request->get('name'),
            'image' => $this->request->get('image'),
            'popularity' => $this->request->get('popularity'),
        ]);

        return $this->success(['genre' => $newGenre]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $this->authorize('update', Genre::class);

        $this->validate($this->request, [
            'name' => Rule::unique('genres')->ignore($id),
            'image' => 'string',
            'popularity' => 'integer|min:1|max:100'
        ]);

        $genre = $this->genre
            ->find($id)
            ->update($this->request->all());

        return $this->success(['genre' => $genre]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $this->authorize('destroy', Genre::class);

        $this->validate($this->request, [
            'ids' => 'required|array|exists:genres,id'
        ]);

        $count = $this->genre->destroy($this->request->get('ids'));

        return $this->success(['count' => $count]);
    }
}