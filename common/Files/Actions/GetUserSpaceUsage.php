<?php namespace Common\Files\Actions;

use Auth;
use Common\Auth\User;
use Illuminate\Http\UploadedFile;
use Common\Settings\Settings;

class GetUserSpaceUsage {

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings) {
        $this->user = Auth::user();
        $this->settings = $settings;
    }

    /**
     * Get disk space that current user is currently using.
     *
     * @return array
     */
    public function execute() {
        return [
            'used' => $this->getSpaceUsed(),
            'available' => $this->getAvailableSpace(),
        ];
    }

    /**
     * Space current user is using in bytes.
     *
     * @return int
     */
    private function getSpaceUsed()
    {
        return (int) $this->user->entries()->sum('file_size');
    }

    /**
     * Maximum available space for current user in bytes.
     *
     * @return int
     */
    private function getAvailableSpace() {

        if ( ! is_null($this->user->available_space)) {
            return $this->user->available_space;
        }

        if (config('common.site.billing_enabled') && $this->user->subscribed()) {
            return $this->user->subscriptions->first()->mainPlan()->available_space;
        }

        return abs($this->settings->get('uploads.available_space'));
    }

    /**
     * Return if user has used up his disk space.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function userIsOutOfSpace(UploadedFile $file) {
        return ($this->getSpaceUsed() + $file->getSize()) > $this->getAvailableSpace();
    }
}