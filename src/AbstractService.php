<?php

namespace pribolshoy\repository;

use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Class AbstractService
 *
 * Abstract class for realization of service object
 * by which we can using Repositories.
 *
 */
abstract class AbstractService extends BaseService implements ServiceInterface
{

    /**
     * Names of items properties which we can call
     * primary keys.
     * @var array
     */
    protected array $primaryKeys = [
        'id',
    ];

    /**
     * Get primary key from item.
     * It can be multiple.
     *
     * @return mixed
     */
    public function getItemPrimaryKey($item)
    {
        $primaryKey = '';

        foreach ($this->primaryKeys as $name) {
            if ($value = $this->getItemAttribute($item, $name)) {
                $primaryKey .= $value;

                if (!$this->isMultiplePrimaryKey()) {
                    return $primaryKey;
                }
            }
        }

        return $primaryKey;
    }

    /**
     * Get attribute value from item.
     * Can be realized in child.
     *
     * @param $item
     * @param string $name
     *
     * @return mixed
     */
    public function getItemAttribute($item, string $name)
    {
        if (is_array($item)
            && array_key_exists($name, $item)
        ) {
            $result = $item[$name];
        } elseif (is_object($item)
            && isset($item->$name)) {
            $result = $item->$name;
        }

        return $result ?? null;
    }

    /**
     * Collects item's primary keys in array.
     *
     * @param array $items
     *
     * @return array
     */
    public function collectItemsPrimaryKeys(array $items): array
    {
        foreach ($items as $item) {
            $result[] = $this->getItemPrimaryKey($item);
        }

        return $result ?? [];
    }

    /**
     * TODO: need?
     * Collects certain item's value in array.
     *
     * @param array $items
     * @param string $name name of item attribute to collect
     *
     * @return array
     */
    protected function collectItemsValue(array $items, string $name): array
    {
        foreach ($items as $item) {
            if ($value = $this->getItemAttribute($item, $name)) {
                $result[] = $value;
            }
        }

        return $result ?? [];
    }

    /**
     * @deprecated
     *
     * @param $item
     * @return string
     */
    public function getHashByItem($item)
    {
        return $this->getItemHash($item);
    }

    /**
     * Get one item by hashtable.
     *
     * @param $itemWithPrimaryKeys array or object with primary keys
     *                             which are used for hashtable keys.
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getByHashtable($itemWithPrimaryKeys)
    {
        // init items if didn't yet
        if (is_null($this->getItems())) {
            $this->getList();
        }

        $key = $this->getItemHashtableStructure()
            ->getByKey($this->getItemHash($itemWithPrimaryKeys));

        return $this->getItemStructure()
            ->getByKey($key);
    }

    /**
     * Process of items sorting.
     * Must be realized in child
     *
     * @param array $items
     *
     * @return mixed
     */
    abstract public function sort(array $items): array;

    /**
     * Resort existing items.
     *
     * @return ServiceInterface
     * @throws exceptions\ServiceException
     */
    public function resort(): object
    {
        if ($items = $this->getItems()) {
            $this->setItems($this->sort($items));
        }
        return $this;
    }
}

