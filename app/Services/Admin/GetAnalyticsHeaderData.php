<?php

namespace App\Services\Admin;

use App\Album;
use App\Artist;
use App\Track;
use App\User;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;

class GetAnalyticsHeaderData implements GetAnalyticsHeaderDataAction
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Album
     */
    private $album;

    /**
     * @var Artist
     */
    private $artist;

    /**
     * @param Track $track
     * @param Album $album
     * @param Artist $artist
     * @param User $user
     */
    public function __construct(Track $track, Album $album, Artist $artist, User $user)
    {
        $this->user = $user;
        $this->track = $track;
        $this->album = $album;
        $this->artist = $artist;
    }

    public function execute()
    {
        return [
            [
                'icon' => 'people',
                'name' => 'Total Users',
                'type' => 'number',
                'value' => $this->user->count(),
            ],
            [
                'icon' => 'audiotrack',
                'name' => 'Total Tracks',
                'type' => 'number',
                'value' => $this->track->count(),
            ],
            [
                'icon' => 'album',
                'name' => 'Total Albums',
                'type' => 'number',
                'value' => $this->album->count(),
            ],
            [
                'icon' => 'mic',
                'name' => 'Total Artists',
                'type' => 'number',
                'value' => $this->artist->count(),
            ],
        ];
    }
}