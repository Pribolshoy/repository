<?php

namespace pribolshoy\repository;

use pribolshoy\repository\interfaces\EntityServiceInterface;
use pribolshoy\repository\traits\CachebleServiceTrait;

/**
 * Class AbstractEntityService
 *
 * Abstract class for realization of service object
 * by which we can using Repositoriess.
 *
 * @package app\repositories
 */
abstract class AbstractService implements EntityServiceInterface
{
    /**
     * Elements queried by repository
     * @var array
     */
    protected array $items = [];

    /**
     * @var array
     */
    protected array $hashtable = [];


    /**
     * @var array Сортировка элементов. Может переопределяться
     */
    protected array $sorting = ['name' => SORT_ASC];

    /**
     * @var string namespace где хранятся репозитории.
     */
    protected ?string $repository_path = "";

    /**
     *
     * @var string namespace класса репозитория.
     * Нужен для создания репозитрия через который
     * сервис будет получать и кешировать элементы.
     */
    protected ?string $repository_class = "";

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init() {}

    protected function getRepositoryClass()
    {
        if (!$this->repository_class) {
            //            throw new ServiceException('Не задан атрибут repository_class');
            throw new \Exception('Не задан атрибут repository_class');
        }
        return $this->repository_path . $this->repository_class;
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * Need child realization
     * @return bool
     */
    protected function updateHashtable()
    {
        return true;
    }

    public function getHashtable()
    {
        return $this->hashtable;
    }

    /**
     * Get value from hashtable by hash
     *
     * @param string $hash
     * @return mixed|null
     */
    public function getHashValue(string $hash)
    {
        $hashtable = $this->getHashtable();

        if ($hashtable
            && array_key_exists($hash, $hashtable)
        ) {
            return $hashtable[$hash];
        }
        return null;
    }

    /**
     * Get item by hash value
     *
     * @param string $hash
     * @return mixed|null
     */
    public function getItemByHash(string $hash)
    {
        if ($key = $this->getHashValue($hash)
            && $this->getItems()
        ) {
            return $this->getItems()[$key] ?? null;
        }
        return null;
    }

    /**
     * @param array $sorting
     * @return $this
     */
    public function setSorting(array $sorting): object
    {
        $this->sorting = $sorting;
        return $this;
    }
}

