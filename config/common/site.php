<?php

return [
    'extra_bootstrap_data' => \App\Services\AppBootstrapData::class,

    'spotify' => [
        'id' => env('SPOTIFY_ID'),
        'secret' => env('SPOTIFY_SECRET')
    ],

    'lastfm' => [
        'key' => env('LASTFM_API_KEY'),
    ],

    'soundcloud' => [
        'key' => env('SOUNDCLOUD_API_KEY')
    ],

    'discogs' => [
        'id' => env('DISCOGS_ID'),
        'secret' => env('DISCOGS_SECRET')
    ]
];