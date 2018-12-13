<?php

return [
    'groups' => [
        'users' => [
            'artists.view' => 1,
            'albums.view' => 1,
            'tracks.view' => 1,
            'genres.view' => 1,
            'lyrics.view' => 1,
            'users.view'  => 1,
            'playlists.create' => 1,
            'localizations.show' => 1,
            'pages.view' => 1,
            'uploads.create' => 1,
        ],
        'guests' => [
            'artists.view' => 1,
            'albums.view' => 1,
            'tracks.view' => 1,
            'genres.view' => 1,
            'lyrics.view' => 1,
            'users.view'  => 1,
            'pages.view' => 1,
        ]
    ],
    'all' => [
        //ARTISTS
        'artists' => [
            'artists.view',
            'artists.create',
            'artists.update',
            'artists.delete',
        ],

        //ALBUMS
        'albums' => [
            'albums.view',
            'albums.create',
            'albums.update',
            'albums.delete',
        ],

        //Tracks
        'tracks' => [
            'tracks.view',
            'tracks.create',
            'tracks.update',
            'tracks.delete',
        ],

        //Genres
        'genres' => [
            'genres.view',
            'genres.create',
            'genres.update',
            'genres.delete',
        ],

        //Lyrics
        'lyrics' => [
            'lyrics.view',
            'lyrics.create',
            'lyrics.update',
            'lyrics.delete',
        ],

        //Playlists
        'playlists' => [
            'playlists.view',
            'playlists.create',
            'playlists.update',
            'playlists.delete',
        ],
    ]
];
