<?php

namespace App\Policies;

use App\Playlist;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;

class PlaylistPolicy
{
    use HandlesAuthorization;

    public function index(User $user, $userId = null)
    {
        return $user->hasPermission('playlists.view') || $user->id === (int) $userId;
    }

    public function show(User $user, Playlist $playlist)
    {
        return ($playlist->public && $user->hasPermission('playlists.view')) || $playlist->editors->contains('id', $user->id);
    }

    public function store(User $user)
    {
        return $user->hasPermission('playlists.create');
    }

    public function update(User $user, Playlist $playlist)
    {
        return $user->hasPermission('playlists.update') || $playlist->editors->contains('id', $user->id);
    }

    public function destroy(User $user, Collection $playlists)
    {
       if ($user->hasPermission('playlists.delete')) return true;

        $canDeleteAll = $playlists->filter(function(Playlist $playlist) use($user) {
            return ! $playlist->editors->contains('id', $user->id);
        })->count() === 0;

        return $canDeleteAll;
    }
}
