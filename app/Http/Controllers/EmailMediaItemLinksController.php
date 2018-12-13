<?php namespace App\Http\Controllers;

use App;
use Mail;
use Illuminate\Http\Request;
use App\Mail\ShareMediaItem;
use Common\Core\Controller;
use Common\Settings\Settings;

class EmailMediaItemLinksController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * SendLinksController constructor.
     *
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * Share specified link via email.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function send() {
        $user = $this->request->user();

        $this->validate($this->request, [
            'emails'   => 'required|array|min:1',
            'emails.*' => 'required|email|nullable',
            'message'  => 'string|nullable'
        ]);

        Mail::queue(new ShareMediaItem(
            $user ? $user->display_name : $this->settings->get('branding.site_name'),
            $this->request->get('message'),
            $this->request->get('emails'),
            $this->request->get('link'),
            $this->request->get('name')
        ));

        return $this->success();
    }
}
