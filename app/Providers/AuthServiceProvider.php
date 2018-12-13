<?php

namespace App\Providers;

use App\Album;
use App\Artist;
use App\Genre;
use App\Lyric;
use App\Playlist;
use App\Track;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Track::class        => \App\Policies\TrackPolicy::class,
        Album::class        => \App\Policies\AlbumPolicy::class,
        Artist::class       => \App\Policies\ArtistPolicy::class,
        Lyric::class        => \App\Policies\LyricPolicy::class,
        Playlist::class     => \App\Policies\PlaylistPolicy::class,
        Genre::class        => \App\Policies\GenrePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
