<?php namespace App\Http\Controllers;

use Storage;
use App\Services\SitemapGenerator;
use Common\Core\Controller;

class SitemapController extends Controller {

    /**
     * @var SitemapGenerator
     */
    private $generator;

    /**
     * Create new SitemapController instance.
     *
     * @param SitemapGenerator $generator
     */
    public function __construct(SitemapGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate a sitemap of all urls of the site.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate()
    {
        $this->authorize('index', 'ReportPolicy');

        $this->generator->generate();

        return $this->success();
    }

    /**
     * Show sitemap index file if it exists.
     *
     * @return mixed
     */
    public function showIndex()
    {
        if (Storage::exists('sitemaps/sitemap-index.xml')) {
            return response(Storage::get('sitemaps/sitemap-index.xml'), 200)->header('Content-Type', 'text/xml');
        }
    }
}
