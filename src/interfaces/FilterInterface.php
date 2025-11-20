<?php

namespace pribolshoy\repository\interfaces;

interface FilterInterface extends UsedByServiceInterface
{
    /**
     * Get list of items.
     *
     * @param array $params Additional parameters
     * @param bool $cache_to Whether to cache result
     * @return array|null
     */
    public function getList(array $params = [], bool $cache_to = true): ?array;

    /**
     * Get items by expression.
     *
     * @param array $attributes Attributes for filtering
     * @return array
     */
    public function getByExp(array $attributes): array;

    /**
     * Get items by multiple attributes.
     *
     * @param array $attributes Attributes for filtering
     * @return array
     */
    public function getByMulti(array $attributes): array;

    /**
     * Get item by attributes.
     *
     * @param array $attributes Attributes for filtering
     * @return mixed|null
     */
    public function getBy(array $attributes);

    /**
     * Get item by ID.
     *
     * @param int $id Item ID
     * @param array $attributes Additional attributes
     * @return mixed|null
     */
    public function getById(int $id, array $attributes = []);

    /**
     * Get items by IDs.
     *
     * @param array $ids Array of item IDs
     * @param array $attributes Additional attributes
     * @return array
     */
    public function getByIds(array $ids, array $attributes = []): array;

    /**
     * Filter item by attributes.
     *
     * @param mixed $item Item to filter
     * @param array $attributes Attributes for filtering
     * @return bool
     */
    public function filterByAttributes($item, array $attributes): bool;
}

