<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\interfaces\CachebleRepositoryInterface;

interface CachebleServiceInterface extends ServiceInterface
{
    /**
     * Check if cache is used.
     *
     * @return bool
     */
    public function isUseCache(): bool;

    /**
     * Set whether to use cache.
     *
     * @param bool $use_cache Whether to use cache
     * @return CachebleServiceInterface
     */
    public function setUseCache(bool $use_cache): CachebleServiceInterface;

    /**
     * Check if alias cache is used.
     *
     * @return bool
     */
    public function useAliasCache(): bool;

    /**
     * Set whether to use alias cache.
     *
     * @param bool $use_alias_cache Whether to use alias cache
     * @return CachebleServiceInterface
     */
    public function setUseAliasCache(bool $use_alias_cache): CachebleServiceInterface;

    /**
     * Get item by alias structure.
     *
     * @param mixed $value Alias value
     * @return mixed
     */
    public function getByAliasStructure($value);

    /**
     * Check if cache exists.
     *
     * @param CachebleRepositoryInterface|null $repository Repository instance
     * @return bool
     */
    public function isCacheExists(?CachebleRepositoryInterface $repository = null): bool;

    /**
     * Initialize storage event.
     *
     * @return bool
     */
    public function initStorageEvent(): bool;

    /**
     * Set hash prefix.
     *
     * @param string $hash_prefix Hash prefix
     * @return self
     */
    public function setHashPrefix(string $hash_prefix): self;

    /**
     * Get hash prefix.
     *
     * @return string
     */
    public function getHashPrefix(): string;

    /**
     * Check if items were fetched from cache.
     *
     * @return bool
     */
    public function isFromCache(): bool;

    /**
     * Set whether items were fetched from cache.
     *
     * @param bool $is_from_cache Whether items were fetched from cache
     * @return CachebleServiceInterface
     */
    public function setIsFromCache(bool $is_from_cache): CachebleServiceInterface;

    /**
     * Get fetching step.
     *
     * @return int
     */
    public function getFetchingStep(): int;

    /**
     * Set fetching step.
     *
     * @param int $fetching_step Fetching step
     * @return self
     */
    public function setFetchingStep(int $fetching_step): self;

    /**
     * Initialize storage.
     *
     * @param RepositoryInterface|null $repository Repository instance
     * @param bool $refresh_repository_cache Whether to refresh repository cache
     * @return CachebleServiceInterface
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false): CachebleServiceInterface;

    /**
     * Clear storage.
     *
     * @param CachebleRepositoryInterface|null $repository Repository instance
     * @param array $params Additional parameters
     * @return bool
     */
    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = []): bool;

    /**
     * Refresh item in cache.
     *
     * @param array $primaryKeyArray Primary key array
     * @return bool
     */
    public function refreshItem(array $primaryKeyArray): bool;

    /**
     * Prepare item after fetching.
     *
     * @param mixed $item Item to prepare
     * @return mixed
     */
    public function prepareItem($item);

    /**
     * Set alias postfix.
     *
     * @param string $alias_postfix Alias postfix
     * @return CachebleServiceInterface
     */
    public function setAliasPostfix(string $alias_postfix): CachebleServiceInterface;

    /**
     * Get alias postfix.
     *
     * @return string
     */
    public function getAliasPostfix(): string;

    /**
     * Get alias attribute name.
     *
     * @return string
     * @throws \Exception
     */
    public function getAliasAttribute(): string;

    /**
     * Get item by alias.
     *
     * @param string $alias Alias value
     * @param array $attributes Additional attributes
     * @return mixed
     */
    public function getByAlias(string $alias, array $attributes = []);

    /**
     * Add cache params for get or set operations.
     *
     * @param string $name 'get' или 'set'
     * @param array $param
     * @return CachebleServiceInterface
     */
    public function addCacheParams(string $name, array $param): CachebleServiceInterface;

    /**
     * Set cache params.
     *
     * @param array $cache_params Должен содержать ключи 'get' и/или 'set'
     * @return CachebleServiceInterface
     */
    public function setCacheParams(array $cache_params): CachebleServiceInterface;

    /**
     * Get cache params.
     *
     * @param string $name 'get' или 'set', или пустая строка для получения всех параметров
     * @return array
     */
    public function getCacheParams(string $name = ''): array;

    /**
     * Delete item from cache storage by primary key.
     *
     * @param string $primaryKey
     * @return bool
     */
    public function deleteItem(string $primaryKey): bool;

    /**
     * Get postfix for ID in cache key.
     *
     * @return string
     */
    public function getIdPostfix(): string;

    /**
     * Get item ID value that will be used as key in cache.
     *
     * @param $item
     * @return mixed
     */
    public function getItemIdValue($item);

    /**
     * Event after item refresh.
     *
     * @param array $primaryKeyArray
     * @return void
     */
    public function afterRefreshItem(array $primaryKeyArray): void;
}

