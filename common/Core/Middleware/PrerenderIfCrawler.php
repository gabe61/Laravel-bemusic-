<?php namespace Common\Core\Middleware;

use Closure;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Common\Core\Seo\BasePrerenderUtils;

abstract class PrerenderIfCrawler
{
    protected $utils;

    protected $crawlerUserAgents = [
        'googlebot',
        'yahoo',
        'bingbot',
        'baiduspider',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'Google-StructuredDataTestingTool',
        'Google-Structured-Data-Testing-Tool',
        'slackbot',
        'YandexBot'
    ];

    /**
     * PrerenderIfCrawler constructor.
     *
     * @param BasePrerenderUtils $utils
     */
    public function __construct(BasePrerenderUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * @param string $type
     * @param Request $request
     * @return Request|view|null
     */
    protected abstract function getResponse($type, Request $request);

    /**
     * Prerender request if it's requested by a crawler.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $type
     * @return Request|View
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if (
            $this->shouldPrerender($request) &&
            $response = $this->getResponse($type, $request)
        ) {
            return $response;
        }

        return $next($request);
    }

    /**
     * Check if request should be prerendered.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldPrerender(Request $request)
    {
        $userAgent = strtolower($request->server->get('HTTP_USER_AGENT'));
        $bufferAgent = $request->server->get('X-BUFFERBOT');

        $shouldPrerender = false;

        if ( ! $userAgent) return false;
        if ( ! $request->isMethod('GET')) return false;

        // prerender if _escaped_fragment_ is in the query string
        if ($request->query->has('_escaped_fragment_')) $shouldPrerender = true;

        // prerender if a crawler is detected
        foreach ($this->crawlerUserAgents as $crawlerUserAgent) {
            if (str_contains($userAgent, strtolower($crawlerUserAgent))) {
                $shouldPrerender = true;
            }
        }

        if ($bufferAgent) $shouldPrerender = true;

        return $shouldPrerender;
    }
}