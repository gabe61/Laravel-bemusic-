<?php namespace App\Http\Controllers;

use App;
use App\Artist;
use Illuminate\Http\Request;
use App\Services\Artists\ArtistAlbumsPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\Controller;

class ArtistAlbumsController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ArtistAlbumsPaginator
     */
    private $paginator;

    /**
     * Create new ArtistController instance.
     *
     * @param Request $request
     * @param ArtistAlbumsPaginator $paginator
     */
	public function __construct(Request $request, ArtistAlbumsPaginator $paginator)
	{
        $this->request = $request;
        $this->paginator = $paginator;
    }

    /**
     * Paginate all artist albums.
     *
     * @param integer $artistId
     * @return LengthAwarePaginator
     */
	public function index($artistId)
	{
		$this->authorize('show', Artist::class);

	    return $this->paginator->paginate($artistId);
	}
}
