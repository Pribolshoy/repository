<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\exceptions\ServiceException;

interface ServiceInterface extends BaseServiceInterface
{
    /**
     * Get item attribute value.
     *
     * @param mixed $item Item (array or object)
     * @param string $name Attribute name
     * @return mixed
     * @throws ServiceException
     */
    public function getItemAttribute($item, string $name);

    /**
     * Get item by hashtable key.
     *
     * @param mixed $key Key (string or array)
     * @param string|null $structureName Structure name
     * @return mixed
     */
    public function getByHashtable($key, ?string $structureName);

    /**
     * Sort items array.
     *
     * @param array $items Items to sort
     * @return array
     */
    public function sort(array $items): array;

    /**
     * Resort items.
     *
     * @return ServiceInterface
     */
    public function resort(): ServiceInterface;

    /**
     * Collect primary keys from items array.
     *
     * @param array $items
     * @return array
     */
    public function collectItemsPrimaryKeys(array $items): array;

    /**
     * Get hash by item.
     *
     * @param $item
     * @return mixed
     */
    public function getHashByItem($item);
}

