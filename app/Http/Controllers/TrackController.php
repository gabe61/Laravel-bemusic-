<?php namespace App\Http\Controllers;

use App;
use App\Track;
use App\Services\Paginator;
use Common\Core\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyTracks;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrackController extends Controller {

	/**
	 * @var Track
	 */
	private $track;

    /**
     * @var Request
     */
    private $request;

    /**
     * TrackController constructor.
     *
     * @param Track $track
     * @param Request $request
     */
    public function __construct(Track $track, Request $request)
	{
		$this->track = $track;
        $this->request = $request;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return LengthAwarePaginator
	 */
	public function index()
	{
        $this->authorize('index', Track::class);

        $params = $this->request->all();
        $params['order_by'] = isset($params['order_by']) ? $params['order_by'] : 'spotify_popularity';

	    return (new Paginator($this->track))->paginate($params);
	}

	/**
	 * Find track matching given id.
	 *
	 * @param  int  $id
	 * @return Track
	 */
	public function show($id)
	{
        $track = $this->track->with('album.artist', 'album.tracks')->findOrFail($id);

	    $this->authorize('show', $track);

	    return $track;
	}

    /**
     * Update existing track.
     *
     * @param int $id
     * @param ModifyTracks $validate
     * @return Track
     */
	public function update($id, ModifyTracks $validate)
	{
		$track = $this->track->findOrFail($id);

		$this->authorize('update', $track);

		$track->fill($this->request->except('album'))->save();

		return $track;
	}

    /**
     * Create a new track.
     *
     * @param ModifyTracks $validate
     * @return Track
     */
    public function store(ModifyTracks $validate)
    {
        $this->authorize('store', Track::class);

        $track = $this->track->create($this->request->all());

        return $track;
    }

	/**
	 * Remove tracks from database.
	 *
	 * @return mixed
	 */
	public function destroy()
	{
		$this->authorize('destroy', Track::class);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

	    return $this->track->destroy($this->request->get('ids'));
	}
}
