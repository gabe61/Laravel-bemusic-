<?php namespace App;

use App\Traits\FormatsPermissions;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Group
 *
 * @property integer $id
 * @property string $name
 * @property string $permissions
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property boolean $default
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group wherePermissions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereDefault($value)
 * @mixin \Eloquent
 * @property int $guests
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereGuests($value)
 */
class Group extends Model
{
    use FormatsPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'permissions', 'default'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden   = ['pivot'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'default' => 'integer', 'guests' => 'integer'];

    /**
     * Get default group for assigning to new users.
     *
     * @return Group|null
     */
    public function getDefaultGroup()
    {
        return $this->where('default', 1)->first();
    }

    /**
     * Users belonging to this group.
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_group');
    }
}
