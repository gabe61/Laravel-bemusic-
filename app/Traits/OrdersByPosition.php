<?php namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;

trait OrdersByPosition {

    /**
     * Order model by position, models with position=0 (default) are last
     *
     * @param Builder $q
     * @return Builder
     */
    public function scopeOrderByPosition(Builder $q)
    {
        return $q->orderBy(DB::raw('position = 0, position'));
    }
}