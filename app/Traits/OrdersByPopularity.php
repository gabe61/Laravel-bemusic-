<?php namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Common\Settings\Settings;

trait OrdersByPopularity {

    /**
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    public function scopeOrderByPopularity(Builder $query, $direction = 'desc')
    {
        $method = \App::make(Settings::class)->get('player.sort_method', 'external');

        $column = $method === 'external' ? 'spotify_popularity' : $this->getLocalField();

        return $query->orderBy($column, $direction);
    }

    private function getLocalField()
    {
        if ($this->getTable() === 'tracks') {
            return 'plays';
        } else {
            return 'views';
        }
    }
}