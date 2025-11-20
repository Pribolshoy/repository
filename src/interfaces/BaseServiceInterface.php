<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\structures\HashtableStructure;

interface BaseServiceInterface
{
    /**
     * Check if primary key is multiple.
     *
     * @return bool
     */
    public function isMultiplePrimaryKey(): bool;

    /**
     * Set primary keys.
     *
     * @param array $primaryKeys Array of primary key names
     * @return BaseServiceInterface
     */
    public function setPrimaryKeys(array $primaryKeys): BaseServiceInterface;

    /**
     * Get item structure.
     *
     * @param bool $refresh Whether to refresh structure
     * @return StructureInterface
     */
    public function getItemStructure(bool $refresh = false):StructureInterface;

    /**
     * Get basic hashtable structure.
     *
     * @param bool $refresh Whether to refresh structure
     * @return HashtableStructure
     */
    public function getBasicHashtableStructure(bool $refresh = false):HashtableStructure;

    /**
     * Get named structures.
     *
     * @return array
     */
    public function getNamedStructures(): array;

    /**
     * Get named structure by name.
     *
     * @param string $name
     * @return StructureInterface|null
     */
    public function getNamedStructure(string $name): ?StructureInterface;

    /**
     * Get repository instance.
     *
     * @param array $params Repository parameters
     * @return RepositoryInterface
     */
    public function getRepository(array $params = []): RepositoryInterface;

    /**
     * Set repository class name.
     *
     * @param string $repository_class Repository class name
     * @return BaseServiceInterface
     */
    public function setRepositoryClass(string $repository_class): BaseServiceInterface;

    /**
     * Get items array.
     *
     * @return array|null
     */
    public function getItems():?array;

    /**
     * Set items array.
     *
     * @param array $items Items array
     * @return void
     */
    public function setItems(array $items): void;

    /**
     * Add item to items array.
     *
     * @param mixed $item Item to add
     * @param bool $replace_if_exists Whether to replace if exists
     * @return BaseServiceInterface
     */
    public function addItem($item, bool $replace_if_exists = true) : BaseServiceInterface;

    /**
     * Get item hash.
     *
     * @param mixed $item Item
     * @return mixed
     */
    public function getItemHash($item);

    /**
     * Hash value.
     *
     * @param mixed $value Value to hash
     * @return string
     */
    public function hash($value) :string;

    /**
     * Get item primary key.
     *
     * @param mixed $item Item
     * @return mixed
     */
    public function getItemPrimaryKey($item);

    /**
     * Get hashtable.
     *
     * @return mixed
     */
    public function getHashtable();

    /**
     * Update hashtable.
     *
     * @return BaseServiceInterface
     */
    public function updateHashtable(): BaseServiceInterface;

    /**
     * Set filter class name.
     *
     * @param string $filter_class Filter class name
     * @return BaseServiceInterface
     */
    public function setFilterClass(string $filter_class): BaseServiceInterface;

    /**
     * Get filter instance.
     *
     * @param bool $refresh Whether to refresh filter
     * @return FilterInterface
     */
    public function getFilter(bool $refresh = false): FilterInterface;

    /**
     * Set sorting parameters.
     *
     * @param array $sorting Sorting parameters
     * @return BaseServiceInterface
     */
    public function setSorting(array $sorting): BaseServiceInterface;

    /**
     * Get list of items.
     *
     * @param array $params Additional parameters
     * @param bool $cache_to Whether to cache result
     * @return array|null
     */
    public function getList(array $params = [], bool $cache_to = true) : ?array;

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
}

