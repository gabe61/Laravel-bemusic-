<?php namespace App\Http\Controllers;

use App;
use App\User;
use App\Playlist;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Common\Core\Controller;

class UserPlaylistsController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * @var App\User
     */
    private $user;

    /**
     * PlaylistController constructor.
     *
     * @param Request $request
     * @param Playlist $playlist
     * @param App\User $user
     */
    public function __construct(Request $request, Playlist $playlist, User $user)
    {
        $this->request = $request;
        $this->playlist = $playlist;

        $this->middleware('auth', ['only' => ['follow', 'unfollow']]);
        $this->user = $user;
    }

    /**
     * Fetch all playlists user has created or followed.
     *
     * @param integer $userId
     * @return Collection
     */
    public function index($userId)
    {
        $this->authorize('index', [Playlist::class, $userId]);

        if ($userId) {
            $user = $this->user->find($userId);
        } else {
            $user = $this->request->user();
        }

        return $user
            ->playlists()
            ->withCount('tracks')
            ->with(['tracks' => function (BelongsToMany $q) {
                return $q->with('album')->limit(1);
            }, 'editors'])->get();
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
