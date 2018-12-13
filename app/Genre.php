<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Genre
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Artist[] $artists
 * @mixin \Eloquent
 */
class Genre extends Model
{
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function artists()
    {
        return $this->belongsToMany('App\Artist', 'genre_artist')
            ->orderByPopularity('desc')
            ->orderBy('spotify_followers', 'desc');
    }

    /**
     * @param string|null $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        // default genre image
        if ( ! $value) {
            $value = "client/assets/images/default/artist_small.jpg";
        }

        // make sure image url is absolute
        if ( ! str_contains($value, '//')) {
            $value = url($value);
        }

        return $value;
    }
}
