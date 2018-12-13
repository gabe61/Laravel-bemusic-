<?php namespace App\Services;

use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class Paginator
{
    /**
     * @var Builder
     */
    private $query;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $defaultOrderColumn = 'updated_at';

    /**
     * @var string
     */
    private $defaultOrderDirection = 'desc';

    /**
     * Search callback provided by caller.
     *
     * @var callable
     */
    private $searchCallback;

    /**
     * Paginator constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    /**
     * Paginate given model.
     *
     * @param array $params
     *
     * @return LengthAwarePaginator
     */
    public function paginate($params)
    {
        $perPage = Arr::get($params, 'per_page', 15);
        $with = array_filter(explode(',', Arr::get($params, 'with', '')));
        $order = Arr::get($params, 'order_by', $this->defaultOrderColumn);
        $orderDir = Arr::get($params, 'order_dir', $this->defaultOrderDirection);
        $searchTerm = Arr::get($params, 'query');
        $page = Arr::get($params, 'page', 1);

        if ( ! empty($with)) $this->query->with($with);

        $this->performSearch($searchTerm);

        $this->query->orderBy($order, $orderDir);

        return new LengthAwarePaginator(
            $this->query->skip(($page - 1) * $perPage)->take($perPage)->get(),
            $this->getTotalCount(),
            $perPage,
            $page
        );
    }

    /**
     * Perform search if search term is specified.
     *
     * @param string $searchTerm
     */
    private function performSearch($searchTerm)
    {
        if ( ! $searchTerm) return;

        if ($this->searchCallback) {
            call_user_func($this->searchCallback, $this->query, $searchTerm);
        } else {
            $this->query->where('name', 'LIKE', "$searchTerm%");
        }
    }

    /**
     * Specify custom search callback.
     *
     * @param callable $callback
     * @return $this
     */
    public function search(callable $callback)
    {
        $this->searchCallback = $callback;
        return $this;
    }

    /**
     * Load specified relation counts with paginator items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function withCount($relations)
    {
        $this->query->withCount($relations);
        return $this;
    }

    /**
     * Load specified relations of paginated items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * Set default order column and direction for paginator.
     *
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'desc')
    {
        $this->defaultOrderColumn = $column;
        $this->defaultOrderDirection = $direction;
        return $this;
    }

    public function getTotalCount()
    {
        $key = "pagination.{$this->model->getTable()}_count";

        return Cache::remember($key, Carbon::now()->addDay(), function () {
            return $this->model->count();
        });
    }
}
