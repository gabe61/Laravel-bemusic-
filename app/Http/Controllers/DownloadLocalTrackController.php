<?php

namespace App\Http\Controllers;

use App\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Common\Core\Controller;
use Common\Settings\Settings;

class DownloadLocalTrackController extends Controller
{
    /**
     * @var Track
     */
    private $track;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * TrackController constructor.
     *
     * @param Track $track
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Track $track, Request $request, Settings $settings)
    {
        $this->track = $track;
        $this->request = $request;
        $this->settings = $settings;
    }

    public function download($id) {
        if ( ! $this->settings->get('player.enable_download')) {
            abort(404);
        }

        $track = $this->track->findOrFail($id);

        $this->authorize('show', $track);

        if ( ! $track->url) abort(404);

        $response = response()->stream(function () use ($track) {
            echo file_get_contents($track->url);
        });

        $ext = pathinfo($track->url, PATHINFO_EXTENSION);
        $name = str_replace('%', '', Str::ascii($track->name)).".$ext";

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name,
            $name
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
