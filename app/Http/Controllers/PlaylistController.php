<?php namespace App\Http\Controllers;

use App;
use App\Playlist;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Jobs\IncrementModelViews;
use App\Http\Requests\ModifyPlaylist;
use Illuminate\Filesystem\FilesystemManager;
use App\Services\Playlists\PlaylistTracksPaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Common\Core\Controller;
use Common\Settings\Settings;


class PlaylistController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * @var PlaylistTracksPaginator
     */
    private $tracksPaginator;

    /**
     * @var FilesystemManager
     */
    private $storage;

    /**
     * PlaylistController constructor.
     *
     * @param Request $request
     * @param Settings $settings
     * @param Playlist $playlist
     * @param FilesystemManager $storage
     * @param PlaylistTracksPaginator $tracksPaginator
     */
    public function __construct(
        Request $request,
        Settings $settings,
        Playlist $playlist,
        PlaylistTracksPaginator $tracksPaginator,
        FilesystemManager $storage
    )
    {
        $this->request = $request;
        $this->settings = $settings;
        $this->playlist = $playlist;
        $this->tracksPaginator = $tracksPaginator;
        $this->storage = $storage;

        $this->middleware('auth', ['except' => ['show']]);
    }

    /**
     * Fetch all playlists user has created or followed.
     *
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', Playlist::class);

        return (new Paginator($this->playlist))
            ->withCount('tracks')
            ->with(['tracks' => function (BelongsToMany $q) {
                return $q->with('album')->limit(1);
            }, 'editors'])
            ->paginate($this->request->all());
    }

    /**
     * Return playlist matching specified id.
     *
     * @param int $id
     * @return array
     */
    public function show($id)
    {
        $playlist = $this->playlist->with('editors')->withCount('tracks')->findOrFail($id);

        $this->authorize('show', $playlist);

        $totalDuration = $playlist->tracks()->sum('tracks.duration');

        dispatch(new IncrementModelViews($playlist->id, 'playlist'));

        return [
            'playlist' => $playlist->toArray(),
            'tracks' => $this->tracksPaginator->paginate($playlist->id),
            'totalDuration' => (int)$totalDuration
        ];
    }

    /**
     * Create a new playlist.
     *
     * @param ModifyPlaylist $validate
     * @return Playlist
     */
    public function store(ModifyPlaylist $validate)
    {
        $this->authorize('store', Playlist::class);

        $playlist = $this->request->user()->playlists()->create($this->request->all(), ['owner' => 1]);

        return $playlist;
    }

    /**
     * Update playlist.
     *
     * @param  int $id
     * @param ModifyPlaylist $validate
     * @return Playlist
     */
    public function update($id, ModifyPlaylist $validate)
    {
        $playlist = $this->playlist->with('editors')->findOrFail($id);

        $this->authorize('update', $playlist);

        $playlist->fill($this->request->all())->save();

        return $playlist;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $ids = $this->request->get('ids');
        $playlists = $this->playlist->with('editors')->whereIn('id', $ids)->get();

        $this->authorize('destroy', [Playlist::class, $playlists]);

        foreach ($playlists as $playlist) {
            if ($playlist->image) {
                $this->storage->disk('public')->delete('playlist-images/' . pathinfo($playlist->image, PATHINFO_FILENAME));
            }

            $playlist->tracks()->detach();
            $playlist->delete();
        }

        return $this->success();
    }

    /**
     * Follow playlist with currently logged in user.
     *
     * @param int $id
     * @return integer
     */
    public function follow($id)
    {
        $playlist = $this->playlist->findOrFail($id);

        $this->authorize('show', $playlist);

        return $this->request->user()->playlists()->sync([$id], false);
    }

    /**
     * Un-Follow playlist with currently logged in user.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow($id)
    {
        $playlist = $this->request->user()->playlists()->find($id);

        $this->authorize('show', $playlist);

        if ($playlist) {
            $this->request->user()->playlists()->detach($id);
        }

        return $this->success();
    }
}
