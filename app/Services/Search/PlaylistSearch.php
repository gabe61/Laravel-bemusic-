<?php namespace App\Services\Search;

use App\Playlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlaylistSearch implements SearchInterface
{
    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * PlaylistSearch constructor.
     *
     * @param Playlist $playlist
     */
    public function __construct(Playlist $playlist)
    {
        $this->playlist = $playlist;
    }

    /**
     * Search playlists in local database.
     *
     * @param string $q
     * @param int $limit
     *
     * @return array
     */
    public function search($q, $limit = 10)
    {
        $playlists = $this->playlist->with(['editors' => function (BelongsToMany $q) {
            return $q->compact();
        }, 'tracks' => function(BelongsToMany $q) {
            return $q->with('album')->limit(1);
        }])
            ->where('public', 1)
            ->where('name', 'like', $q.'%')
            ->has('tracks')
            ->limit(20)
            ->get();

        return $this->setPlaylistImage($playlists)->toArray();
    }

    /**
     * Make sure all playlists have an image.
     *
     * @param Collection $playlists
     * @return Collection
     */
    private function setPlaylistImage($playlists)
    {
        return $playlists->map(function(Playlist $playlist) {
            if ( ! $playlist->image && $playlist->tracks->isNotEmpty()) {
                $playlist->image = $playlist->tracks->first()->album->image;
            }

            if (! $playlist->image) {
                $playlist->image = url('assets/images/default/artist_small.jpg');
            }

            unset($playlist->tracks);

            return $playlist;
        });
    }
}