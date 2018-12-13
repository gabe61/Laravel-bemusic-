<?php namespace App;

use App\Traits\OrdersByPopularity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Track
 *
 * @property int $id
 * @property string $name
 * @property string $album_name
 * @property int $number
 * @property int $duration
 * @property array $artists
 * @property string|null $youtube_id
 * @property int $spotify_popularity
 * @property int $album_id
 * @property string|null $temp_id
 *  * @property boolean $auto_update
 * @property string|null $url
 * @property-read \App\Album $album
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Track[] $artist
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Playlist[] $playlists
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @mixin \Eloquent
 */
class Track extends Model {

    use OrdersByPopularity;

    public $timestamps = false;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'formatted_duration', 'plays', 'lyric'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['fully_scraped', 'temp_id', 'pivot'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'       => 'integer',
        'album_id' => 'integer',
        'number'   => 'integer',
        'spotify_popularity' => 'integer',
        'duration' => 'integer',
        'auto_update' => 'boolean'
    ];

    /**
     * Convert artists from string to array. *|* is a delimiter.
     *
     * @param string $artists
     * @return array
     */
    public function getArtistsAttribute($artists)
    {
        return explode('*|*', $artists);
    }

    /**
     * Many to many relationship with user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }

    /**
     * Many to one relationship with album model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function album()
    {
        return $this->belongsTo('App\Album');
    }

    /**
     * Many to many relationship with playlist model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function playlists()
    {
        return $this->belongsToMany('App\Playlist')->withPivot('position');
    }

    /**
     * This track's lyric.
     *
     * @return HasOne
     */
    public function lyric()
    {
        return $this->hasOne('App\Lyric');
    }
}
