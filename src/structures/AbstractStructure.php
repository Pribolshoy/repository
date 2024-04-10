<?php

namespace pribolshoy\repository\structures;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\traits\HashableStructure;
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
     * @param array $params
     *
     * @return object
     */
    public function addParams(array $params):object
    {
        foreach ($params as $name => $value) {
            if (property_exists(static::class, $name)) {
                $this->$name = $value;
            }
        }

        return $this;
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

    /**
     * @param $item
     * @param null $key
     *
     * @return object
     */
    public function addItem($item, $key = null):object
    {
        if (!is_null($key)) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * Get one item by key.
     *
     * @param int|string $key
     *
     * @return mixed|null
     */
    public function getByKey($key)
    {
        $items = $this->getItems();

        if (!is_null($key)
            && $key !== ''
            && $items
        ) {
            // if structure use HashableStructure trait
            if (in_array(HashableStructure::class, class_uses(static::class))) {
                /** @var HashableStructure $hashable */
                $hashable = $this;
                $key = $hashable->getHash($key);
            }

            return $items[$key] ?? null;
        }

        return null;
    }

    /**
     * Get items by keys.
     *
     * @param array $keys
     *
     * @return mixed|null
     */
    public function getByKeys($keys)
    {
        $items = $this->getItems();

        if (is_array($keys)
            && $keys
            && $items
        ) {
            // if structure use HashableStructure trait
            if (in_array(HashableStructure::class, class_uses(static::class))
            ) {
                foreach ($keys as &$key) {
                    /** @var HashableStructure $hashable */
                    $hashable = $this;
                    $key = $hashable->getHash($key);
                }
            }

            return array_intersect_key($items, array_flip($keys)) ?? [];
        }

        return null;
    }

}

