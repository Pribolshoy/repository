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
     * @var boolean Lazy загрузка - связи не подгружаются при выборке.
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
     * Object which will using for search.
     * For example, it could be ActiveRecord in Yii2
     *
     * @var object
     */
    protected ?object $model = null;

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
    public array $items = [];

    protected ?int $total_count = null;

    /**
     * @var boolean Нужно ли получать количество всех строк
     */
    public bool $need_total = true;

    /**
     * @var boolean Должна ли быть выборка массива
     */
    protected bool $is_array = false;

    public function __construct(array $params = [], ?string $model_class = null)
    {
        $this->params = $params;
        if ($model_class) $this->model_class = $model_class;

        $this->makeQueryBuilder();
        $this->collectFilter();
    }

    /**
     * Установка значения выборки в виде массива
     * @param $need_total
     * @return $this
     */
    public function setNeedTotal($need_total)
    {
        $this->need_total = $need_total;
        return $this;
    }

    /**
     * Получение значения выборки всего количества элементов
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
     * Установка флага - выборка элементов как массив.
     * Для возможности использования текучего интерфейса.
     *
     * @param $is_array
     * @return $this
     */
    public function setIsArray($is_array): self
    {
        $this->is_array = $is_array;
        return $this;
    }

    /**
     * Получение значения выборки в виде массива
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
    ): object {
        $this->params = $params;
        if ($clear_filter) $this->filter = [];
        if ($update_filter) $this->collectFilter();
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getFilters(): array
    {
        return $this->filter;
    }

    public function getFilter(string $name)
    {
        return $this->getFilters()[$name] ?? null;
    }

    /**
     * @param bool $refresh flag that we need to refresh
     *                      query object from previous conditions.
     * @return bool|mixed
     */
    public function search(bool $refresh = true)
    {
        if ($refresh) $this->makeQueryBuilder();

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
        if (!$this->model) {
            $this->makeQueryBuilder();
        }

        return $this->model;
    }

    /**
     * Actions before the search.
     *
     * return $this
     */
    protected function beforeFetch()
    {
        // if lazy load is deactivate - connections dont adding
        if (!$this->lazy_load) $this->addConnections();
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
    protected function afterFetch() {return $this;}

    /**
     * Configuration of entity connections with other entitiesю
     *
     * return $this
     */
    protected function addConnections() {return $this;}

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
    protected function modifyParams() :object {return $this;}

    /**
     * Gets table name from entity or other way.
     * @return string
     */
    abstract public function getTableName() :string;

    /**
     * Standart filter collecting from params, which may be
     * common for most part of entities which you will fetch to.
     */
    abstract protected function defaultFilter();

    /**
     * Individual filter collecting from params, for a specific entity.
     */
    protected function filter() {}

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
    protected function addPreQueries() {return $this;}

    /**
     * В методе происходит установка всех параметров where
     * которые будут в запросе
     *
     * return $this
     */
    protected function addQueries()  {return $this;}

    /**
     * В методе устанавливаются limit и offset
     * запроса перед выборкой.
     *
     * return $this
     */
    protected function addLimitAndOffset() :object {return $this;}

    /**
     *
     *
     * @return mixed
     *
     * return $this
     */
    protected function getTotal() {return $this;}

    /**
     * Creating of model object for fetching elements by
     * model_class property.
     *
     * @return object
     * @throws RepositoryException
     */
    public function getModel() :object
    {
        if ($this->model_class) {
            return new $this->model_class();
        } else {
            throw new RepositoryException('Не задан класс сущности для репозитория');
        }

    }
}

