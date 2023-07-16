<?php

namespace pribolshoy\repository;

use pribolshoy\exceptions\RepositoryException;
use pribolshoy\repository\interfaces\RepositoryInterface;

/**
 * Class AbstractRepository
 *
 * Abstract class for realisation of searching a specific entity.
 *
 * @package app\repositories
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Class of model object which will using for search.
     *
     * @var string
     */
    protected ?string $model_class = null;

    /**
     * Object which will using for search.
     *
     * @var object
     */
    protected ?object $model = null;

    /**
     * Params which will using for collecting filters.
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Properties which will using for building a query.
     * It collects from params property.
     * @var array
     */
    public array $filter = [];

    /**
     * Selected items by search.
     *
     * @var array
     */
    public array $items = [];

    public function __construct(array $params = [], ?string $model_class = null)
    {
        $this->params = $params;
        if ($model_class) $this->model_class = $model_class;
    }

    /**
     * @param array $params
     * @param bool $update_filter
     * @param bool $clear_filter
     *
     * @return $this
     */
    public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object
    {
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

    /**
     * @return bool|mixed
     */
    public function search()
    {
        $this->beforeFetch();
        $this->fetch();
        $this->afterFetch();

        return $this->items;
    }

    /**
     * Actions before the search.
     * Default is empty.
     *
     * return $this
     */
    protected function beforeFetch()  {return $this;}

    /**
     * Executing of elements fetching using builded model.
     */
    abstract protected function fetch(): void;

    /**
     * Actions after the search.
     * Default is empty.
     *
     * return $this
     */
    protected function afterFetch() {return $this;}

    /**
     * Collecting of filters.
     *
     * @return $this
     */
    protected function collectFilter()
    {
        $this->defaultFilter();
        $this->filter();
        return $this;
    }

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

