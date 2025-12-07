<?php


namespace App\Services;

use App\Constants\AppConst;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class BaseService
{
    const EXCLUDE_PARAMS = ['page', 'limit'];

    /** @var $model Model */
    protected $model;

    protected $query;

    public function __construct()
    {
        $this->setModel();
        $this->query = $this->model->newQuery();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public abstract function model();

    /**
     * Set Eloquent Model to instantiate
     *
     * @return void
     */
    private function setModel(): void
    {
        $newModel = App::make($this->model());

        if (!$newModel instanceof Model)
            throw new \RuntimeException("Class {$newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        $this->model = $newModel;
    }

    public function getTableName()
    {
        return $this->model->getTable();
    }

    /**
     * Retrieve the specified resource.
     *
     * @param int $id
     * @param array $relations
     * @param array $appends
     * @param array $hidden
     * @param bool $withTrashed
     * @return Model
     */
    public function show(int $id, array $relations = [], array $appends = [], array $hidden = [], bool $withTrashed = false, array $withCount = []): Model
    {
        if ($withTrashed) {
            $this->query->withTrashed();
        }
        return $this->query
            ->with($relations)
            ->withCount($withCount)
            ->findOrFail($id)
            ->setAppends($appends)
            ->makeHidden($hidden);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @return Model|bool
     * @throws Exception
     */
    public function store(array $attributes)
    {
        $parent = $this->query->create($attributes);
        $relations = [];

        foreach (array_filter($attributes, [$this, 'isRelation']) as $key => $models) {
            if (method_exists($parent, $relation = Str::camel($key))) {
                $relations[] = $relation;
                $this->syncRelations($parent->$relation(), $models, false);
            }
        }
        if (count($relations)) {
            $parent->load($relations);
        }

        return $parent->push() ? $parent : false;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @return Model|bool
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function update($parent, array $attributes)
    {
        if (is_integer($parent)) {
            $parent = $this->query->findOrFail($parent);
        }
        $parent->fill($attributes);
        $relations = [];

        foreach (array_filter($attributes, [$this, 'isRelation']) as $key => $models) {
            if (method_exists($parent, $relation = Str::camel($key))) {
                $relations[] = $relation;
                $this->syncRelations($parent->$relation(), $models);
            }
        }
        if (count($relations)) {
            $parent->load($relations);
        }

        return $parent->push() ? $parent : false;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Model|int $item
     * @param bool $force
     * @return bool
     *
     */
    public function destroy($item, bool $force = false): bool
    {
        if (is_integer($item)) {
            $item = $this->query->findOrFail($item);
        }
        return $item->{$force ? 'forceDelete' : 'delete'}();
    }

    /**
     * @param $id
     * @return bool
     */
    public function restore($id): bool
    {
        return $this->query->withTrashed()->findOrFail($id)->restore();
    }

    /**
     * @param array $attrs
     * @return Builder|Model|null|object
     */
    public function findBy(array $attrs)
    {
        return $this->query->where($attrs)->first();
    }

    public function findAll(array $columns = ['*'])
    {
        return $this->query->get(is_array($columns) ? $columns : func_get_args());
    }

    public function paginate($params = null, array $relations = [], array $columns = [], bool $toPagination = true, Builder $query = null)
    {
        if (!$query) {
            $query = $this->query;
        }
        $query = $this->buildBasicQuery($query, $params, $relations);
        if (isset($params['val']) && isset($params['label'])) {
            $columns = [$params['val'], $params['label']];
        }
        if ($columns) {
            $query->select($columns);
        }
        if (!$toPagination) {
            return $query->get();
        } else {
            return $this->toPagination($query);
        }
    }


    protected function buildBasicQuery(Builder $query, array $params = null, array $relations = [], bool $withTrashed = false): Builder
    {
        $params = $params ?: request()->toArray();
        if ($relations && count($relations)) {
            $query->with($relations);
        }
        if ($withTrashed && in_array(SoftDeletes::class, class_uses($this->model)) && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }
        if (method_exists($this, 'addFilter')) {
            $this->addFilter();
        }

        $this->basicFilter($query, $params);

        $sort = $this->getSort($params);
        if ($sort) {
            foreach ($sort as $order => $direction) {
                $query->orderBy($order, $direction);
            }
        }
        return $query;
    }

    protected function basicFilter(Builder $query, array $params = []): Builder
    {
        $params = $params ?: request()->toArray();
        foreach ($params as $key => $value) {
            if (in_array($key, self::EXCLUDE_PARAMS) || ($value == '' && $value == NULL)) {
                continue;
            }
            $suffixes = ['__equal', '__like', '__from', '__to', '__in', '__relation','__date'];
            $tableName = $this->getTableName();
            $column = $tableName . '.' . $this->removeSuffix($key, $suffixes, $condition);
            if ($condition === '__like') {
                $query->where($column, 'LIKE', '%' . $value . '%');
            } elseif ($condition === '__from') {
                $query->where($column, '>=', $value);
            } elseif ($condition === '__to') {
                $query->where($column, '<=', $value);
            } elseif ($condition === '__equal') {
                is_array($value) ? $query->whereIn($column, $value) : $query->where($column, $value);
            } elseif ($condition === '__date') {
                $query->whereDate($column, $value);
            } elseif ($condition === '__in' && is_array($value)) {
                $query->whereIn($column, $value);
            } elseif ($condition === '__relation' && is_array($value)) {
                foreach ($value as $relation => $relValue) {
                    if ($relValue) {
                        $query->whereHas($column, function ($builder) use ($relation, $relValue) {
                            $this->basicFilter($builder, [$relation => $relValue]);
                        });
                    }
                }
            }
        }
        return $query;
    }

    protected function removeSuffix($key, $suffixes, &$condition = null)
    {
        foreach ($suffixes as $suf) {
            if (Str::endsWith($key, $suf)) {
                $condition = $suf;
                return Str::substr($key, 0, -strlen($suf));
            }
        }
        $condition = null;
        return $key;
    }

    /**
     * @param Builder $query
     * @param null $params
     * @return array
     */
    public function toPagination(Builder $query): array
    {
        $perPage = request('per_page', AppConst::DEFAULT_PER_PAGE);
        $paginate = $query->paginate($perPage);
        return [
            'data' => $paginate->items(),
            'total' => $paginate->total(),
            'last_page' => $paginate->lastPage(),
            'from' => $paginate->firstItem(),
            'to' => $paginate->lastItem(),
            'current_page' => $paginate->currentPage()
        ];
    }

    public function getSort($params): array
    {
        $sortBy = $params['order_by'] ?? request()->input('order_by');
        $sortDirection = $params['order_type'] ?? request()->input('order_type');
        $sortBy = is_array($sortBy) ? $sortBy : [$sortBy];
        $sortDirection = is_array($sortDirection) ? $sortDirection : [$sortDirection];
        $sort = array_filter(array_combine($sortBy, $sortDirection));
        $tableName = $this->getTableName();
        if (!$sort) {
            $sort = [$tableName . '.id' => 'desc'];
        }
        return $sort;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isRelation($value): bool
    {
        return is_array($value) || $value instanceof Model;
    }

    /**
     * @param Relation $relation
     * @param array | Model $conditions
     * @param bool $detaching
     * @return void
     * @throws Exception
     */
    private function syncRelations(Relation $relation, $conditions, bool $detaching = true): void
    {
        $conditions = is_array($conditions) ? $conditions : [$conditions];
        $relatedModels = [];
        foreach ($conditions as $condition) {
            if ($condition instanceof Model) {
                $relatedModels[] = $condition;
            } else if (is_array($condition)) {
                $relatedModels[] = $relation->firstOrCreate($condition);
            } else if (is_int($condition)) {
                $relatedModels[] = $condition;
            }
        }

        if ($relation instanceof BelongsToMany && method_exists($relation, 'sync')) {
            $relation->sync($this->parseIds($relatedModels), $detaching);
        } else if ($relation instanceof HasMany | $relation instanceof HasOne) {
            $relation->saveMany($relatedModels);
        } else {
            throw new Exception('Relation not supported');
        }
    }

    /**
     * @param array $models
     * @return array
     */
    private function parseIds(array $models): array
    {
        $ids = [];
        foreach ($models as $model) {
            $ids[] = $model instanceof Model ? $model->getKey() : $model;
        }

        return $ids;
    }
}
