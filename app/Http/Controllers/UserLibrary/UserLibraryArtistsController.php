<?php namespace App\Http\Controllers\UserLibrary;

use App\Track;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Common\Core\Controller;

class UserLibraryArtistsController extends Controller {

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
     * Get All artists in user library.
     *
     * @return array
     */
    public function index()
    {
        $tracks = $this->request->user()->load(['tracks.album.artist' => function(BelongsTo $q) {
            return $q->select('id', 'name', 'image_small', 'image_large');
        }])->tracks->toArray();

        $artists = [];

        foreach ($tracks as $track) {
            $artist = $track['album']['artist'];
            $artist['number_of_tracks'] = 1;

            if ( ! $artist) continue;

            if (isset($artists[$artist['id']])) {
                $artists[$artist['id']]['number_of_tracks']++;
            } else {
                $artists[$artist['id']] = $artist;
            }
        }

        return array_values($artists);
    }
}
