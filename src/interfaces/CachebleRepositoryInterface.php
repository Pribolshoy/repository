<?php

namespace pribolshoy\repository\interfaces;

interface CachebleRepositoryInterface extends RepositoryInterface
{
    /**
     * Set maximum number of pages to cache.
     *
     * @param int $num Maximum number of pages
     * @return CachebleRepositoryInterface
     */
    public function setMaxCachedPage($num) :CachebleRepositoryInterface;

    /**
     * Get maximum number of pages to cache.
     *
     * @return int
     */
    public function getMaxCachedPage() :int;

    /**
     * Get driver parameters.
     *
     * @return array
     */
    public function getDriverParams(): array;

    /**
     * Check if repository is cacheable.
     *
     * @return bool
     */
    public function isCacheble() :bool;

    /**
     * Set active cache flag.
     *
     * @param bool $activate Whether to activate cache
     * @return CachebleRepositoryInterface
     */
    public function setActiveCache(bool $activate = true): CachebleRepositoryInterface;

    /**
     * Check if cache is active.
     *
     * @return bool
     */
    public function isCacheActive(): bool;

    /**
     * Get cache duration in seconds.
     *
     * @return int
     */
    public function getCacheDuration(): int;

    /**
     * Set cache duration in seconds.
     *
     * @param int $duration Cache duration in seconds
     * @return CachebleRepositoryInterface
     */
    public function setCacheDuration(int $duration) :CachebleRepositoryInterface;

    /**
     * Get total hash prefix.
     *
     * @return string
     */
    public function getTotalHashPrefix(): string;

    /**
     * Get hash prefix.
     *
     * @return string
     */
    public function getHashPrefix(): string;

    /**
     * Set hash name for cache.
     *
     * @param string $hash_name Hash name
     * @return CachebleRepositoryInterface
     */
    public function setHashName(string $hash_name) :CachebleRepositoryInterface;

    /**
     * Get hash name for cache.
     *
     * @param bool $refresh Whether to refresh hash name
     * @param bool $use_params Whether to use params in hash
     * @param bool $save_to Whether to save hash name
     * @return string
     */
    public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true) :string;

    /**
     * Set data to cache.
     *
     * @param mixed $data Data to cache
     * @param array $params Additional parameters
     * @return CachebleRepositoryInterface
     */
    public function setToCache($data, array $params = []): CachebleRepositoryInterface;

    /**
     * Get data from cache.
     *
     * @param bool $refresh Whether to refresh cache
     * @param array $params Additional parameters
     * @return mixed
     */
    public function getFromCache(bool $refresh = false, array $params = []);

    /**
     * Delete data from cache.
     *
     * @param array $params Additional parameters
     * @return CachebleRepositoryInterface
     */
    public function deleteFromCache(array $params = []): CachebleRepositoryInterface;

    /**
     * Get hash from array.
     *
     * @param array $data Data to hash
     * @param bool $hashToMd5 Whether to use MD5 hash
     * @return string
     */
    public function getHashFromArray(array $data, bool $hashToMd5 = false) :string;

    /**
     * Set cache driver instance.
     * Useful for testing and dependency injection.
     *
     * @param CacheDriverInterface $driver
     * @return CachebleRepositoryInterface
     */
    public function setDriver(CacheDriverInterface $driver): CachebleRepositoryInterface;

    /**
     * Get driver using for cache repository.
     * Default Redis
     *
     * @return CacheDriverInterface
     * @throws \Exception
     */
    public function getDriver(): CacheDriverInterface;
}

