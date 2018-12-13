<?php namespace App;

use App\Traits\OrdersByPopularity;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Artist
 *
 * @property int $id
 * @property string $name
 * @property int|null $spotify_followers
 * @property int $spotify_popularity
 * @property string $image_small
 * @property string|null $image_large
 * @property int $fully_scraped
 * @property \Carbon\Carbon|null $updated_at
 *  * @property boolean $auto_update
 * @property string|null $bio
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Album[] $albums
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Genre[] $genres
 * @property-read string $image_big
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Artist[] $similar
 * @mixin \Eloquent
 */
class Artist extends Model {
    use OrdersByPopularity;

    /**
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'spotify_popularity' => 'integer',
        'fully_scraped' => 'boolean',
        'auto_update' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $hidden = ['fully_scraped', 'temp_id', 'pivot'];

    /**
     * @var array
     */
    protected $guarded = ['id', 'views'];

    public function albums()
    {
    	return $this->hasMany(Album::class);
    }

    public function similar()
    {
        return $this->belongsToMany(Artist::class, 'similar_artists', 'artist_id', 'similar_id')
            ->orderByPopularity('desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_artist');
    }

    /**
     * Decode artist biography attribute.
     *
     * @param string $value
     * @return array
     */
    public function getBioAttribute($value) {
        if ( ! $value) return [];
        return json_decode($value, true);
    }

    /**
     * Get small artist image or default image.
     *
     * @param $value
     * @return string
     */
    public function getImageSmallAttribute($value)
    {
        if ($value) return $value;

        return asset('client/assets/images/default/artist_small.jpg');
    }

    /**
     * Get large artist image or default image.
     *
     * @param $value
     * @return string
     */
    public function getImageLargeAttribute($value)
    {
        if ($value) return $value;

        return asset('client/assets/images/default/artist-big.jpg');
    }
}
