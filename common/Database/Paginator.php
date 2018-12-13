<?php namespace Common\Database;

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
        $params = $this->toCamelCase($params);

        $with = array_filter(explode(',', Arr::get($params, 'with', '')));
        $withCount = array_filter(explode(',', Arr::get($params, 'withCount', '')));
        $searchTerm = Arr::get($params, 'query');
        $order = $this->getOrder($params);
        $perPage = Arr::get($params, 'perPage', 15);
        $page = (int) Arr::get($params, 'page', 1);

        //load specified relations and counts
        if ( ! empty($with)) $this->query->with($with);
        if ( ! empty($withCount)) $this->query->withCount($withCount);

        //search
        if ($searchTerm) $this->query->where('name', 'like', "$searchTerm%");

        //order
        $this->query->orderBy($order['col'], $order['dir']);

        //paginate
        return new LengthAwarePaginator(
            with(clone $this->query)->skip(($page - 1) * $perPage)->take($perPage)->get(),
            $this->query->count(),
            $perPage,
            $page
        );
    }

    /**
     * @return Builder
     */
    public function query() {
        return $this->query;
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
     * Add a basic where clause to the query.
     *
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->query->where($column, $operator, $value, $boolean);
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

    /**
     * Extract order for paginator query from specified params.
     *
     * @param $params
     * @return array
     */
    private function getOrder($params) {
        //order provided as single string: "column|direction"
        if (Arr::has($params, 'order')) {
            list($orderCol, $orderDir) = explode('|', $params['order']);
        } else {
            $orderCol = Arr::get($params, 'orderBy', $this->defaultOrderColumn);
            $orderDir = Arr::get($params, 'orderDir', $this->defaultOrderDirection);
        }

        return ['dir' => $orderDir, 'col' => $orderCol];
    }

    private function toCamelCase($params)
    {
        return collect($params)->keyBy(function($value, $key) {
            return camel_case($key);
        })->toArray();
    }
}
