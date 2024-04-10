<?php

namespace pribolshoy\repository\interfaces;

interface StructureInterface extends UsedByServiceInterface
{
    public function addParams(array $params):object;

    public function getItems():?array;

    public function setItems(array $items);

    public function addItem($item, $key = null):object;

    public function getByKey($key);

    public function getByKeys($keys);
}

