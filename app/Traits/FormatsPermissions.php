<?php namespace App\Traits;

trait FormatsPermissions {

    /**
     * Encode permissions into json string.
     *
     * @param array $value
     */
    public function setPermissionsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['permissions'] = json_encode($value);
        } else {
            $this->attributes['permissions'] = $value;
        }
    }

    /**
     * Return decoded permissions.
     *
     * @param string $value
     * @return array
     */
    public function getPermissionsAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }
}