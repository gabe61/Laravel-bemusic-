<?php

return [
    //artists
    ['method' => 'DELETE', 'name' => 'artists'],
    ['method' => 'PUT', 'name' => 'artists/{id}'],
    ['method' => 'POST', 'name' => 'artists'],

    //albums
    ['method' => 'DELETE', 'name' => 'albums'],
    ['method' => 'PUT', 'name' => 'albums/{id}'],
    ['method' => 'POST', 'name' => 'albums'],

    //tracks
    ['method' => 'DELETE', 'name' => 'tracks'],
    ['method' => 'PUT', 'name' => 'tracks/{id}'],
    ['method' => 'POST', 'name' => 'tracks'],

    //lyrics
    ['method' => 'DELETE', 'name' => 'lyrics'],
    ['method' => 'PUT', 'name' => 'lyrics/{id}'],
    ['method' => 'POST', 'name' => 'lyrics'],

    //playlists
    ['method' => 'DELETE', 'name' => 'playlists'],

    //sitemap
    ['method' => 'POST', 'name' => 'admin/sitemap/generate']
];