<?php namespace App\Http\Controllers\UserLibrary;

use App\Track;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\Controller;

class UserLibraryTracksController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Track
     */
    private $track;

    /**
     * UserLibraryController constructor.
     *
     * @param Request $request
     * @param Track $track
     */
    public function __construct(Request $request, Track $track)
    {
        $this->middleware('auth');

        $this->request = $request;
        $this->track = $track;
    }

    /**
     * Add specified tracks to user's library.
     */
    public function add()
    {
        $this->request->user()->tracks()->sync($this->request->get('ids'), false);
        return $this->success();
    }

    /**
     * Remove specified tracks from user's library.
     */
    public function remove()
    {
       $this->request->user()->tracks()->detach($this->request->get('ids'));
        return $this->success();
    }

    /**
     * Paginate tracks in user library.
     *
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $limit = $this->request->get('limit', 50);

        /** @var Builder $query */
        $query = $this->request->user()->tracks()->with(['album.artist' => function(BelongsTo $q) {
            $q->select(['id', 'name']);
        }]);

        if ($this->request->get('query')) {
            $query->where('name', 'LIKE', $this->request->get('query').'%');

            $query->orWhereHas('album', function(Builder $q) {
                return $q->where('name', 'LIKE', $this->request->get('query').'%')
                    ->orWhereHas('artist', function(Builder $q) {
                        return $q->where('name', 'LIKE', $this->request->get('query').'%');
                    });
            });
        }

        switch ($this->request->get('order')) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'duration':
                $query->orderByDesc('duration');
                break;
            case 'artist_name':
                $query->orderByDesc('artists');
                break;
            case 'album_name':
                $query->orderByDesc('album_name');
                break;
            default:
                $query->orderByDesc('track_user.created_at');
        }

        $paginator = $query->paginate($limit);

        $paginator->map(function(Track $track) {
            $track->added_at = $track->pivot->created_at->diffForHumans();
            return $track;
        });

        return $paginator;
    }
}
