<?php namespace App\Http\Controllers;

use App;
use App\Album;
use App\Jobs\IncrementModelViews;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyAlbums;
use App\Services\Albums\AlbumsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\Controller;

class AlbumController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AlbumsRepository
     */
    private $repository;

    /**
     * @param AlbumsRepository $repository
     * @param Request $request
     */
	public function __construct(AlbumsRepository $repository, Request $request)
	{
        $this->request = $request;
        $this->repository = $repository;
    }

	/**
	 * Paginate all albums.
	 *
	 * @return LengthAwarePaginator
	 */
	public function index()
	{
		$this->authorize('index', Album::class);

	    return $this->repository->paginate($this->request->all());
	}

    /**
     * Get album matching specified ID.
     *
     * @param number $id
     * @return Album
     */
    public function show($id)
    {
        $this->authorize('show', Album::class);

        $album = $this->repository->load($id);

        dispatch(new IncrementModelViews($album->id, 'album'));

        return $album;
    }

    /**
     * Update existing album.
     *
     * @param  int $id
     * @param ModifyAlbums $validate
     * @return Album
     */
	public function update($id, ModifyAlbums $validate)
	{
		$this->authorize('update', Album::class);

	    return $this->repository->update($id, $this->request->all());
	}

    /**
     * Create a new album.
     *
     * @param ModifyAlbums $validate
     * @return Album
     */
    public function store(ModifyAlbums $validate)
    {
        $this->authorize('store', Album::class);

        return $this->repository->create($this->request->all());
    }

	/**
	 * Remove specified albums.
	 *
	 * @return mixed
	 */
	public function destroy()
	{
	    $this->authorize('destroy', Album::class);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

	    $this->repository->delete($this->request->get('ids'));

	    return $this->success();
	}
}
