<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrackPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('tracks.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('tracks.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('tracks.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('tracks.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('tracks.delete');
    }
}
