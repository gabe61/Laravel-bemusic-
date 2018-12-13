<?php

namespace Common\Core\Policies;

use Common\Auth\User;
use Common\Files\FileEntry;
use Illuminate\Support\Arr;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Check if current user can view specified entries.
     *
     * @param User $user
     * @param array $entryIds
     * @param int $userId
     * @return bool
     */
    public function index(User $user, array $entryIds = null, $userId = null)
    {
        // user has permissions to view all entries
        if ($user->hasPermission('files.view')) {
            return true;
        }

        // check if all entries of specified user can be viewed
        if ( ! $entryIds && (int) $userId === $user->id) {
            return true;
        }

        // check if specific entries can be viewed by user
        return $this->userHasPermission($user, 'view', $entryIds);
    }

    public function show(User $user, FileEntry $entry)
    {
        return $user->hasPermission('files.view') || $this->userHasPermission($user, 'view', [$entry->id]);
    }

    /**
     * Check if user can create entry.
     *
     * @param User $user
     * @param int $parentId
     * @param int|null $userId
     * @return bool
     */
    public function store(User $user, $parentId = null, $userId = null)
    {
        if ($user->hasPermission('files.create')) {
            return true;
        }

        //check if user can modify parent entry (if specified)
        if ($parentId) {
            return $this->userHasPermission($user, 'edit', [$parentId]);
        }

        return $user->id === (int) $userId;
    }

    public function update(User $user, array $entryIds)
    {
        return $user->hasPermission('files.update') || $this->userHasPermission($user, 'edit', $entryIds);
    }

    public function destroy(User $user, array $entryIds)
    {
        if ( ! $entryIds || $user->hasPermission('files.delete')) {
            return true;
        }

        //check if user owns all of the specified entries
        $count = $user->entries()
            ->withTrashed()
            ->whereIn('file_entries.id', $entryIds)
            ->wherePivot('owner', true)
            ->count();

        return $count === count($entryIds);
    }

    private function userHasPermission(User $user, $permission, $entryIds)
    {
        // check if user has edit permissions for all specified entries
        $entries = $user->entries()
            ->withPivot(['owner', 'permissions'])
            ->whereIn('file_entries.id', $entryIds)
            ->get();

        return count(array_filter($entryIds, function($entryId) use($entries, $permission) {
                $entry = $entries->find($entryId);

                //user has no access to this entry at all
                if ( ! $entry) return false;

                //user is the owner of this entry
                if ($entry->pivot->owner) return true;

                // user was granted specified permission by file owner
                return Arr::get($entry->pivot->permissions, $permission);
            })) === count($entryIds);
    }
}
