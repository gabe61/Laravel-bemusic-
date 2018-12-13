<?php namespace Common\Core\Controllers;

use Artisan;
use Common\Core\Controller;

class CacheController extends Controller {

    /**
     * Clear all application cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
	public function clear()
	{
        $this->authorize('index', 'ReportPolicy');

	    Artisan::call('cache:clear');

        return $this->success();
	}
}
