<?php

namespace pribolshoy\repository;

use pribolshoy\repository\interfaces\EntityServiceInterface;

/**
 * Class AbstractEntityService
 *
 * Abstract class for realization of service object
 * by which we can using items collection.
 *
 * @package app\repositories
 */
abstract class AbstractItemService implements EntityServiceInterface
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
     * AbstractItemService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init() {}

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
}

