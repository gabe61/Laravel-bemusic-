<?php namespace App\Http\Controllers;

use App;
use App\Artist;
use App\Http\Requests\ModifyArtists;
use App\Jobs\IncrementModelViews;
use Illuminate\Http\Request;
use App\Services\Artists\ArtistsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\Controller;

class ArtistController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ArtistsRepository
     */
    private $repository;

    /**
     * Create new ArtistController instance.
     *
     * @param Request $request
     * @param ArtistsRepository $repository
     */
	public function __construct(Request $request, ArtistsRepository $repository)
	{
        $this->request = $request;
        $this->repository = $repository;
    }

	/**
	 * Paginate all artists.
	 *
	 * @return LengthAwarePaginator
	 */
	public function index()
	{
        $this->authorize('index', Artist::class);

	    return $this->repository->paginate($this->request->all());
	}

    /**
     * Return artist matching specified id or name.
     *
     * @param integer $nameOrId
     * @return array
     */
    public function show($nameOrId)
    {
        $this->authorize('show', Artist::class);

        if ($this->request->has('by_name')) {
            $data = $this->repository->getByName($nameOrId, $this->request->all());
        } else {
            $data = $this->repository->getById($nameOrId, $this->request->all());
        }

        dispatch(new IncrementModelViews($data['artist']['id'], 'artist'));

        return $data;
    }

    /**
     * Create a new artist.
     *
     * @param ModifyArtists $validate
     * @return Artist
     */
    public function store(ModifyArtists $validate)
    {
        $this->authorize('store', Artist::class);

        return $this->repository->create($this->request->all());
    }

    /**
     * Update existing artist.
     *
     * @param  int $id
     * @param ModifyArtists $validate
     * @return Artist
     */
	public function update($id, ModifyArtists $validate)
	{
		$this->authorize('update', Artist::class);

	    return $this->repository->update($id, $this->request->all());
	}

    /**
     * Remove specified artists from database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
	public function destroy()
	{
		$this->authorize('destroy', Artist::class);

	    $this->validate($this->request, [
		    'ids'   => 'required|array',
		    'ids.*' => 'required|integer'
        ]);

	    $this->repository->delete($this->request->get('ids'));

		return $this->success();
	}
}
