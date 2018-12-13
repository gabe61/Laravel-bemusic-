<?php namespace App\Http\Controllers;

use App\Lyric;
use App\Track;
use App\Services\Paginator;
use App\Services\HttpClient;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Common\Core\Controller;

class LyricsController extends Controller {

    /**
     * @var Lyric
     */
    private $lyric;

    /**
     * @var HttpClient
     *
     */
    private $http;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Request
     */
    private $request;

    /**
     * LyricsController constructor.
     *
     * @param Lyric $lyric
     * @param Track $track
     * @param HttpClient $http
     * @param Request $request
     */
    public function __construct(Lyric $lyric, Track $track, HttpClient $http, Request $request)
    {
        $this->http = $http;
        $this->lyric = $lyric;
        $this->track = $track;
        $this->request = $request;
    }

    /**
     * Paginate all existing lyrics.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', Lyric::class);

        return (new Paginator($this->lyric))->search(function(Builder $query, $term) {
            $query->whereHas('track', function(Builder $query) use($term) {
                return $query->where('tracks.name', 'like', "$term%");
            });
        })->paginate($this->request->all());
    }

    /**
     * Get lyrics for song from external site.
     *
     * @param integer $trackId
     * @return Lyric
     */
	public function show($trackId)
	{
        $this->authorize('show', Lyric::class);

	    $lyric = $this->lyric->where('track_id', $trackId)->first();

        if ( ! $lyric) {
            $lyric = $this->fetchLyrics($trackId);
        }

        return $lyric;
	}

    /**
     * Create new lyric for specified track.
     *
     * @return Lyric
     */
	public function store()
    {
        $this->authorize('store', Lyric::class);

        $this->validate($this->request, [
            'text' => 'required|string',
            'track_id' => 'required|integer|exists:tracks,id',
        ]);

        return $this->lyric->create([
            'track_id' => $this->request->get('track_id'),
            'text'     => $this->request->get('text')
        ]);
    }

    /**
     * Update specified lyric.
     *
     * @param integer $id
     * @return Lyric
     */
    public function update($id)
    {
        $this->authorize('update', Lyric::class);

        $this->validate($this->request, [
            'text' => 'required|string',
            'track_id' => 'required|integer|exists:tracks,id',
        ]);

        $lyric = $this->lyric->findOrFail($id);

        $lyric->update([
            'track_id' => $this->request->get('track_id'),
            'text'     => $this->request->get('text')
        ]);

        return $lyric;
    }

    /**
     * Delete specified lyrics.
     */
    public function destroy()
    {
        $this->authorize('destroy', Lyric::class);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

        $this->lyric->destroy($this->request->get('ids'));
    }

    /**
     * Fetch lyrics from lyrics wikia.
     *
     * @param integer $trackId
     * @return Lyric
     */
	private function fetchLyrics($trackId)
    {
        $track = $this->track->with('album.artist')->findOrFail($trackId);

        $trackName  = $track->name;
        $artistName = $track->album->artist->name;

        $response = $this->http->get("http://lyrics.wikia.com/api.php?action=lyrics&artist=$artistName&song=$trackName&fmt=realjson");

        if ( ! isset($response['url']) || ! $response['url'] || $response['lyrics'] === 'Not found') {
            abort(404);
        }

        $html = $this->http->get($response['url']);

        preg_match("/<div class='lyricbox'>(.+?)<div class='lyricsbreak'>/", $html, $matches);

        if ( ! isset($matches[1])) {
            abort(404);
        }

        $noTags = strip_tags($matches[1], '<br>');

        $special = preg_replace_callback(
            "/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
            },
            $noTags
        );

        $text = html_entity_decode($special);

        return $this->lyric->create([
            'track_id' => $trackId,
            'text'     => $text
        ]);
    }
}
