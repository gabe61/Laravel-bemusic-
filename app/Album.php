<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Album
 *
 * @property int $id
 * @property string $name
 * @property string|null $release_date
 * @property string $image
 * @property int $artist_id
 * @property int $spotify_popularity
 * @property int $fully_scraped
 * @property string|null $temp_id
 * @property boolean $auto_update
 * @property-read \App\Artist|null $artist
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Track[] $tracks
 * @mixin \Eloquent
 */
class Album extends Model {

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'albums';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'artist_id'     => 'integer',
        'fully_scraped'  => 'boolean',
        'spotify_popularity' => 'integer',
        'auto_update' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'views'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['fully_scraped', 'temp_id'];

    /**
     * Artist this album belongs to.
     *
     * @return BelongsTo
     */
    public function artist()
    {
    	return $this->belongsTo('App\Artist');
    }

    /**
     * Tracks that belong to this album.
     *
     * @return HasMany
     */
    public function tracks()
    {
    	return $this->hasMany('App\Track')->orderBy('number');
    }

    /**
     * Get album image or default image.
     *
     * @param $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        if ($value) return $value;

        return asset('client/assets/images/default/album.png');
    }
}
