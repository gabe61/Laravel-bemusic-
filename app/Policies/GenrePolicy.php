<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GenrePolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('genres.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('genres.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('genres.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('genres.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('genres.delete');
    }
}
