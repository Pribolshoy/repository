<?php

namespace pribolshoy\repository\structures;

use pribolshoy\repository\interfaces\StructureInterface;

/**
 * Class HashtableCollisionStructure
 *
 */
class HashtableCollisionStructure extends HashtableStructure
{
    protected array $group_keys = [];

    protected array $collision_keys = [];

    protected ?array $items_to_set = null;

    /**
     * @return array
     */
    public function getGroupKeys(): array
    {
        return $this->group_keys;
    }

    /**
     * @param $item
     *
     * @return string|null
     */
    private function getGroupKey($item): ?string
    {
        $groupKeys = $this->group_keys;
        $itemGroupKey = '';

        $implodedGroupKeys = implode('', $groupKeys);

        if (is_array($item) && in_array($implodedGroupKeys, $item)) {
            return $item[$implodedGroupKeys] ?? null;
        }

        foreach ($groupKeys as $groupKey) {
            $itemGroupKey .= $this->getService()
                ->getItemAttribute($item, $groupKey);
        }

        if (!is_null($itemGroupKey)) {
            return (string)$itemGroupKey;
        }

        return null;
    }

    /**
     * @param $itemPositionKey
     * @param null $item
     *
     * @return string|null
     */
    private function getCollisionKey($itemPositionKey = null, $item = null):?string
    {
        $collisionKeys = $this->collision_keys;
        $itemCollisionKey = '';

        $implodedCollisionKeys = implode('', $collisionKeys);

        if (is_array($item) && in_array($implodedCollisionKeys, $item)) {
            return $item[$implodedCollisionKeys] ?? null;
        }

        foreach ($collisionKeys as $collisionKey) {
            if (!$item && $itemPositionKey
                && $this->items_to_set
            ) {
                $item = $this->items_to_set[$itemPositionKey];
            } else if (!$item) {
                continue;
            }

            $itemCollisionKey .= $this->getService()
                ->getItemAttribute($item, $collisionKey);
        }

        if (mb_strlen($itemCollisionKey)) {
            return (string)$itemCollisionKey;
        }

        return null;
    }

    /**
     * @override
     *
     * @param array $items
     *
     * @return StructureInterface
     */
    public function setItems(array $items): StructureInterface
    {
        $this->items = [];

        $this->items_to_set = $items;

        foreach ($items as $key => $item) {
            $this->addItem($key, $this->getGroupKey($item));
        }

        $this->items_to_set = null;

        return $this;
    }

    /**
     * @override
     *
     * @param $item
     * @param int|string|null $key
     *
     * @return StructureInterface
     */
    public function addItem($item, $key = null): StructureInterface
    {
        $itemCollisionKey = $this->getCollisionKey($item);

        $this->items[$key][$itemCollisionKey] = $item;
        return $this;
    }

    /**
     * @override
     *
     * @param $key array with item keys
     *
     * @return mixed|null
     */
    public function getByKey($key)
    {
        if (!is_null($key)
            && $items = $this->getItems()
        ) {

            $itemGroupKey = $this->getGroupKey($key);
            $itemCollisionKey = $this->getCollisionKey(null, $key);

            if (!is_null($itemCollisionKey)) {
                return $items[$itemGroupKey][$itemCollisionKey] ?? null;
            }

            return $items[$itemGroupKey] ?? null;
        }

        return null;
    }

    /**
     * @override
     *
     * @param $key array with item keys
     *
     * @return mixed|null
     */
    public function getByKeys($key)
    {
        if (($items = $this->getItems())
            && is_array($key)
        ) {
            $itemGroupKey = $this->getGroupKey($key);
            $itemCollisionKey = $this->getCollisionKey(null, $key);

            if (!is_null($itemCollisionKey)) {
                return $items[$itemGroupKey][$itemCollisionKey] ?? null;
            }

            return $items[$itemGroupKey] ?? null;
        }

        return null;
    }
}

