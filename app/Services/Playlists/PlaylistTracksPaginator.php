<?php namespace App\Services\Playlists;

use App\Track;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlaylistTracksPaginator
{
    /**
     * @var Track
     */
    private $track;

    /**
     * PlaylistTracksPaginator constructor.
     *
     * @param Track $track
     */
    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    /**
     * Paginate all specified playlist's tracks.
     *
     * @param integer $playlistId
     * @return LengthAwarePaginator
     */
    public function paginate($playlistId)
    {
        return $this->track
            ->with(['album.artist' => function(BelongsTo $q) {
                return $q->select('id', 'name');
            }])
            ->join('playlist_track', 'tracks.id', '=', 'playlist_track.track_id')
            ->join('playlists', 'playlists.id', '=', 'playlist_track.playlist_id')
            ->where('playlists.id', '=', $playlistId)
            ->select('tracks.*')
            ->orderBy('playlist_track.position', 'asc')
            ->paginate(50);
    }
}