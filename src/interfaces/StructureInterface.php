<?php

namespace pribolshoy\repository\interfaces;

interface StructureInterface extends UsedByServiceInterface
{
    /**
     * @param array $params
     * @return StructureInterface
     */
    public function addParams(array $params):StructureInterface;

    /**
     * @return array|null
     */
    public function getItems():?array;

    /**
     * @param array $items
     * @return StructureInterface
     */
    public function setItems(array $items): StructureInterface;

    /**
     * @param $item
     * @param null $key
     * @return StructureInterface
     */
    public function addItem($item, $key = null):StructureInterface;

    /**
     * @param $key
     * @return mixed
     */
    public function getByKey($key);

    /**
     * @param array $keys
     *
     * @return mixed
     */
    public function getByKeys(array $keys);
}

