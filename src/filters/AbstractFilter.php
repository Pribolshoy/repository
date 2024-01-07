<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;

/**
 * Class AbstractFilter
 *
 * @package app\repositories
 */
abstract class AbstractFilter
{
    protected ?AbstractService $service = null;

    public function __construct(AbstractService $service)
    {
        $this->service = $service;
    }

    /**
     * @param AbstractService|null $service
     */
    public function setService(?AbstractService $service): void
    {
        $this->service = $service;
    }

    /**
     * @return AbstractService|EnormousCachebleService|null
     */
    public function getService(): ?AbstractService
    {
        return $this->service;
    }

    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param int $id
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getById(int $id, array $attributes = [])
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [])
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }
}

