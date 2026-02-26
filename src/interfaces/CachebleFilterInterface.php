<?php

namespace pribolshoy\repository\interfaces;

/**
 * Interface for filters that support cache operations by ID.
 */
interface CachebleFilterInterface extends FilterInterface
{
    /**
     * Save entity to cache by primary key (key extracted from item via getItemIdValue).
     *
     * @param object|array $item Entity to save
     * @param \pribolshoy\repository\interfaces\CachebleRepositoryInterface|null $repository Repository (if null, obtained via service)
     *
     * @return bool Success
     */
    public function setItemToCache($item, ?CachebleRepositoryInterface $repository = null): bool;

    /**
     * Save multiple entities to cache.
     *
     * @param array $items Array of entities to save (uses getItemIdValue for each key)
     * @param \pribolshoy\repository\interfaces\CachebleRepositoryInterface|null $repository Repository (if null, obtained via service)
     *
     * @return int Number of successfully cached items
     */
    public function setItemsToCache(array $items, ?CachebleRepositoryInterface $repository = null): int;
}
