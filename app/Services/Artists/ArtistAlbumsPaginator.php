<?php namespace App\Services\Artists;

use App\Album;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArtistAlbumsPaginator
{
    /**
     * @var Album
     */
    private $album;

    /**
     * AlbumRepository constructor.
     *
     * @param Album $album
     */
    public function __construct(Album $album)
    {
        $this->album = $album;
    }

    /**
     * Paginate all specified artist's albums.
     *
     * First order by number of tracks, so all albums
     * with less then 5 tracks (singles) are at
     * the bottom, then order by album release date.
     *
     * @param integer $artistId
     * @return LengthAwarePaginator
     */
    public function paginate($artistId)
    {
        $prefix = DB::getTablePrefix();

        return $this->album
            ->with('tracks')
            ->where('artist_id', $artistId)
            ->selectRaw("{$prefix}albums.*")
            ->leftjoin('tracks', 'tracks.album_id', '=', 'albums.id')
            ->groupBy('albums.id')
            ->orderByRaw("COUNT({$prefix}tracks.id) > 5 desc, {$prefix}albums.release_date desc")
            ->paginate(5);
    }
}