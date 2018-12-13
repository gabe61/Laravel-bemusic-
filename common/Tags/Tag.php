<?php

namespace Common\Tags;

use Common\Files\FileEntry;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $casts = ['id' => 'integer'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function files()
    {
        return $this->morphedByMany(FileEntry::class, 'taggable');
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function attachEntries($ids, $userId = null)
    {
        if ($userId) {
            $ids = collect($ids)->mapWithKeys(function($id) use($userId) {
                return [$id => ['user_id' => $userId]];
            });
        }

        $this->files()->syncWithoutDetaching($ids);
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function detachEntries($ids, $userId = null)
    {
        $query = $this->files();

        if ($userId) {
            $query->wherePivot('user_id', $userId);
        }

        $query->detach($ids);
    }
}
