<?php namespace App\Services;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AppBootstrapData
{
    /**
     * Get data needed to bootstrap the application.
     *
     * @param $bootstrapData
     * @return array
     */
    public function get($bootstrapData)
    {
        if ( ! isset($bootstrapData['user'])) return $bootstrapData;

        $bootstrapData = $this->getUserTracks($bootstrapData);
        $bootstrapData = $this->getUserPlaylists($bootstrapData);
        $this->loadUserFollowedUsers($bootstrapData);

        return $bootstrapData;
    }

    /**
     * Load users that current user is following.
     *
     * @param array $bootstrapData
     * @return array
     */
    private function loadUserFollowedUsers($bootstrapData)
    {
        $bootstrapData['user'] = $bootstrapData['user']->load(['followedUsers' => function(BelongsToMany $q) {
            return $q->select('users.id', 'users.avatar');
        }]);

        return $bootstrapData;
    }

    /**
     * Get ids of all tracks in current user's library.
     *
     * @param array $bootstrapData
     * @return array
     */
    private function getUserTracks($bootstrapData)
    {
        $bootstrapData['tracks'] = $bootstrapData['user']->tracks()->pluck('tracks.name', 'tracks.id')->keys()->toArray();
        return $bootstrapData;
    }

    /**
     * Get ids of all tracks in current user's library.
     *
     * @param array $bootstrapData
     * @return array
     */
    private function getUserPlaylists($bootstrapData)
    {
        $bootstrapData['playlists'] = $bootstrapData['user']
            ->playlists()
            ->with(['editors' => function(BelongsToMany $q) {
                return $q->compact();
            }])
            ->select('playlists.id', 'playlists.name')
            ->get()
            ->toArray();

        return $bootstrapData;
    }
}
