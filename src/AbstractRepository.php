<?php

namespace pribolshoy\repository;

use pribolshoy\repository\exceptions\RepositoryException;
use pribolshoy\repository\interfaces\RepositoryInterface;

/**
 * Class AbstractRepository
 *
 * Abstract class for realization of searching specific entity.
 *
 * @package app\repositories
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Lazy loading of connections
     *
     * @var boolean Lazy load of connections.
     */
    public bool $lazy_load = false;

    /**
     * Class of model object which will using for search.
     * It needs for implementing self::model
     *
     * @var string
     */
    protected ?string $model_class = null;

    /**
     * Query builder object which will using for search.
     * For example, it could be ActiveQuery in Yii2
     *
     * @var null|object
     */
    protected ?object $queryBuilder = null;

    /**
     * Cached query builder instance.
     * Created once and reused unless force flag is set.
     *
     * @var null|object
     */
    protected ?object $queryBuilderInstance = null;

    /**
     * Params which will using for external collecting filters.
     * For example, GET params.
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Properties which will using for building a query.
     * It collects from self::params property.
     * @var array
     */
    public array $filter = [];

    /**
     * Selected items by search.
     *
     * @var array
     */
    public ?array $items = null;

    protected ?int $total_count = null;

    /**
     * Should we get info about total count of entities.
     *
     * @var boolean
     */
    public bool $need_total = true;

    /**
     * Should we fetch results as array.
     *
     * @var boolean
     */
    protected bool $is_array = false;

    public function __construct(array $params = [], ?string $model_class = null)
    {
        $this->params = $params;
        if ($model_class) {
            $this->model_class = $model_class;
        }

        $this->makeQueryBuilder();
        $this->collectFilter();
    }

    /**
     * @param $need_total
     *
     * @return $this
     */
    public function setNeedTotal($need_total)
    {
        $this->need_total = $need_total;
        return $this;
    }

    /**
     * @return bool
     */
    public function getNeedTotal(): bool
    {
        return $this->need_total;
    }

    /**
     * @param int|null $total_count
     * @return $this
     */
    public function setTotalCount(?int $total_count): self
    {
        $this->total_count = $total_count;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->total_count;
    }

    /**
     * @param $is_array
     *
     * @return $this
     */
    public function setIsArray($is_array): self
    {
        $this->is_array = $is_array;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsArray(): bool
    {
        return $this->is_array;
    }


    /**
     * @param array $params
     * @param bool $update_filter
     * @param bool $clear_filter
     *
     * @return $this
     */
    public function setParams(
        array $params,
        bool $update_filter = false,
        bool $clear_filter = false
    ): RepositoryInterface {
        $this->params = $params;
        if ($clear_filter) {
            $this->filter = [];
        }
        if ($update_filter) {
            $this->collectFilter();
        }
        return $this;
    }

    /**
     * Get params property.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get filters property.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filter;
    }

    /**
     * Get filter by name.
     *
     * @param string $name Filter name
     * @return mixed
     */
    public function getFilter(string $name)
    {
        return $this->getFilters()[$name] ?? null;
    }

    /**
     * Получить параметр по имени
     *
     * @param string $name Имя параметра
     * @return mixed
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Проверить существование параметра
     *
     * @param string $name Имя параметра
     * @return bool
     */
    public function existsParam(string $name): bool
    {
        if (!isset($this->params[$name])) {
            return false;
        }
        if (!$this->getParams()[$name]) {
            return false;
        }
        return true;
    }

    /**
     * Проверить существование фильтра
     *
     * @param string $name Имя фильтра
     * @return bool
     */
    public function existsFilter(string $name): bool
    {
        if (!isset($this->filter[$name])) {
            return false;
        }
        if (!$this->filter[$name]) {
            return false;
        }
        return true;
    }

    /**
     * Добавление значения из self::params в self::filter при
     * условии что оно существует
     *
     * @param $value
     * @param string $default_value
     * @param bool $append
     *
     * @return array|void|string
     */
    public function addFilterValueByParams($value, $default_value = '', $append = true)
    {
        // если существует такой параметр
        if (isset($this->params[$value])) {
            if (is_array($this->params[$value])) {

                if (!empty($this->filter[$value]) && $append) {

                    if (is_array($this->filter[$value])) {
                        $this->filter[$value] = array_merge($this->filter[$value], $this->params[$value]);
                    } else {
                        $this->filter[$value] = array_merge([$this->filter[$value]], $this->params[$value]);
                    }

                } else {
                    $this->filter[$value] = $this->params[$value];
                }

            } else {
                $parts = explode(',', $this->params[$value]);

                if (count($parts) > 1) {

                    if (!empty($this->filter[$value]) && $append) {

                        if (is_array($this->filter[$value])) {
                            $this->filter[$value] = array_merge($this->filter[$value], $parts);
                        } else {
                            $this->filter[$value] = array_merge([$this->filter[$value]], $parts);
                        }

                    } else {
                        $this->filter[$value] = $parts;
                    }

                } else {
                    if (!empty($this->filter[$value]) && $append) {

                        if (is_array($this->filter[$value])) {
                            $this->filter[$value] = array_merge($this->filter[$value], is_array($this->params[$value]) ?: [$this->params[$value]]);
                        } else {
                            $this->filter[$value] = array_merge([$this->filter[$value]], is_array($this->params[$value]) ?: [$this->params[$value]]);
                        }

                    } else {
                        $this->filter[$value] = $this->params[$value];
                    }
                }
            }
        } elseif (strlen($default_value)) {
            $this->filter[$value] = $default_value;
        } elseif (is_bool($default_value)) {
            $this->filter[$value] = $default_value;
        }

        return $this->getFilter($value);
    }

    /**
     * Присоединить значение к фильтру
     *
     * @param $filter_key
     * @param $value
     * @param bool $append
     * @return array|void|string
     */
    public function addFilterValue($filter_key, $value, $append = true)
    {
        if (!empty($this->filter[$filter_key]) && $append) {

            if (is_array($this->filter[$filter_key])) {
                $this->filter[$filter_key] = array_merge($this->filter[$filter_key], [$value]);
            } else {
                $this->filter[$filter_key] = array_merge([$this->filter[$filter_key]], [$value]);
            }

        } else {
            $this->filter[$filter_key] = $value;
        }

        return $this->filter[$filter_key];
    }


    /**
     * @param bool $refresh flag that we need to refresh
     *                      query object from previous conditions.
     * @return bool|mixed
     */
    public function search(bool $refresh = true)
    {
        if ($refresh) {
            $this->makeQueryBuilder();
        }

        $this->beforeFetch();
        $this->fetch();
        $this->afterFetch();

        return $this->items;
    }

    /**
     * Making object for query building.
     * Must be realized in child.
     * @return $this
     */
    abstract protected function makeQueryBuilder();

    /**
     * Get object for query building.
     *
     * @return object
     */
    public function getQueryBuilder()
    {
        if (!$this->queryBuilder) {
            $this->makeQueryBuilder();
        }

        return $this->queryBuilder;
    }

    /**
     * Actions before the search.
     *
     * return $this
     */
    protected function beforeFetch()
    {
        // if lazy load is deactivate - connections don't adding
        if (!$this->lazy_load) {
            $this->addConnections();
        }
        $this->addQueries();
        return $this;
    }

    /**
     * Executing of elements fetching.
     */
    abstract protected function fetch(): object;

    /**
     * Actions after the search.
     * Default is empty.
     *
     * return $this
     */
    protected function afterFetch()
    {
        return $this;
    }

    /**
     * Configuration of entity connections with other entities.
     *
     * return $this
     */
    protected function addConnections()
    {
        return $this;
    }

    /**
     * Collecting of filters.
     *
     * @return $this
     */
    protected function collectFilter()
    {
        $this->modifyParams();
        $this->defaultFilter();
        $this->filter();
        $this->addPreQueries();
        return $this;
    }


    /**
     * Any modification of params before collecting of filter.
     *
     * @return object
     */
    protected function modifyParams(): object
    {
        return $this;
    }

    /**
     * Gets table name from entity or other way.
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * Standart filter collecting from params, which may be
     * common for most part of entities which you will fetch to.
     */
    abstract protected function defaultFilter();

    /**
     * Individual filter collecting from params, for a specific entity.
     */
    protected function filter()
    {
    }

    /**
     * В методе происходит подготовка параметров для
     * выборки where, но которые должны быть получены
     * с помощью предварительных выборок.
     *
     * Например, если для выборки необходимы данные
     * которые можно получить только одним или несколькими
     * отдельными запросами, то они складываются в этот метод.
     *
     * return $this
     */
    protected function addPreQueries()
    {
        return $this;
    }

    /**
     * В методе происходит установка всех параметров where
     * которые будут в запросе
     *
     * return $this
     */
    protected function addQueries()
    {
        return $this;
    }

    /**
     * В методе устанавливаются limit и offset
     * запроса перед выборкой.
     *
     * return $this
     */
    protected function addLimitAndOffset(): object
    {
        return $this;
    }

    /**
     *
     *
     * @return mixed
     *
     * return $this
     */
    protected function getTotal()
    {
        return $this;
    }

    /**
     * Getting query builder instance for fetching elements by
     * model_class property.
     *
     * Instance is created once and cached. Use force flag to recreate it.
     *
     * @param bool $force Force recreation of instance even if cached
     * @return object
     * @throws RepositoryException
     */
    public function getQueryBuilderInstance(bool $force = false): object
    {
        if ($this->queryBuilderInstance !== null && !$force) {
            return $this->queryBuilderInstance;
        }

        if ($this->model_class) {
            $this->queryBuilderInstance = new $this->model_class();
            return $this->queryBuilderInstance;
        } else {
            throw new RepositoryException('Не задан класс сущности для репозитория');
        }
    }
}
