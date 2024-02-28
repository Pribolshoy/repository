<?php

namespace pribolshoy\repository\structures;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\traits\UsedByServiceTrait;

/**
 * Class AbstractStructure
 *
 */
abstract class AbstractStructure implements StructureInterface
{
    use UsedByServiceTrait;

    protected ?array $items = null;

    public function __construct(AbstractService $service)
    {
        $this->service = $service;
    }

    /**
     * @return array
     */
    public function getItems():?array
    {
        return $this->items;
    }

    /**
     * @param array $items
     *
     * @return StructureInterface
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    public function addItem($item, $key = null):object
    {
        $this->items[$key] = $item;
    }

    public function getByKey($key)
    {
        $items = $this->getItems();

        if (!is_null($key)
            && $key !== ''
            && $items
            && array_key_exists($key, $items)
        ) {
            return $items[$key];
        }

        return null;
    }

}

