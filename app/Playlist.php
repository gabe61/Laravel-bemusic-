<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Playlist
 *
 * @property int $id
 * @property string $name
 * @property int $public
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Track[] $tracks
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Playlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Playlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Playlist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Playlist wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Playlist whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $editors
 */
class Playlist extends Model {

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'     => 'integer',
        'public' => 'integer',
    ];

    public function getImageAttribute($value)
    {
        if ( ! $value || str_contains($value, 'images/default')) return null;
        return $value;
    }

    /**
     * Get users that have permissions to edit this playlist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function editors()
    {
        return $this->belongsToMany('App\User')->wherePivot('owner', 1);
    }

    /**
     * Many to many relationship with track model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tracks()
    {
        return $this->belongsToMany('App\Track')->withPivot('position');
    }
}
