<?php

namespace pribolshoy\repository;

use Exception;
use pribolshoy\repository\Config;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CacheDriverInterface;
use pribolshoy\repository\traits\CatalogTrait;

/**
 * Class AbstractRepository
 *
 * Abstract class for realization of searching specific entity.
 *
 * @package app\repositories
 */
abstract class AbstractCachebleRepository extends AbstractRepository implements CachebleRepositoryInterface
{
    use CatalogTrait;

    /**
     * Get driver using for cache repository.
     * Default Redis
     * @var string
     */
    protected ?string $driver = 'redis';

    protected ?string $driver_path = '';

    protected array $driver_params = [];

    protected ?CacheDriverInterface $driver_instance = null;

    /**
     * Is active caching.
     * If we don't need caching of some entity
     * we can disable this attr in entity class.
     * @var bool
     */
    protected bool $active_cache = true;

    /**
     * Hash key from caching of items.
     * @var string|null
     */
    protected ?string $hash_name = null;

    /**
     * Cache durability.
     * Default 3 hours
     */
    protected int $cache_duration = 10800;

    /**
     * Max numbers of page to cache in catalog.
     * @var integer
     */
    protected int $max_cached_page = 4;

    /**
     * @param int $num
     * @return $this
     */
    public function setMaxCachedPage($num): CachebleRepositoryInterface
    {
        $this->max_cached_page = $num;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxCachedPage(): int
    {
        return $this->max_cached_page;
    }

    /**
     * @return array
     */
    public function getDriverParams(): array
    {
        return $this->driver_params;
    }

    /**
     * Set cache driver instance.
     * Useful for testing and dependency injection.
     *
     * @param CacheDriverInterface $driver
     * @return $this
     */
    public function setDriver(CacheDriverInterface $driver): CachebleRepositoryInterface
    {
        $this->driver_instance = $driver;
        return $this;
    }

    /**
     * Get driver using for cache repository.
     * Default Redis
     *
     * @return CacheDriverInterface
     * @throws Exception
     */
    public function getDriver(): CacheDriverInterface
    {
        if ($this->driver_instance) {
            return $this->driver_instance;
        }

        if ($this->driver) {
            $class = $this->driver_path . ucfirst($this->driver) . 'Driver';
            if (class_exists($class)) {
                return $this->driver_instance = new $class($this->getDriverParams());
            }
        }
        throw new Exception('Driver is not defined or not valid');
    }

    /**
     * Assertion the we can cache actual results.
     *
     * @return bool
     */
    public function isCacheble(): bool
    {
        if (
            $this->isCacheActive()
            && $this->page <= $this->getMaxCachedPage()
            && $this->getHashName()
        ) {
            return true;
        }
        return false;
    }


    /**
     * @param bool $activate
     *
     * @return $this
     */
    public function setActiveCache(bool $activate = true): CachebleRepositoryInterface
    {
        $this->active_cache = $activate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCacheActive(): bool
    {
        return $this->active_cache;
    }

    /**
     * @param int $duration
     * @return $this
     */
    public function setCacheDuration(int $duration): CachebleRepositoryInterface
    {
        $this->cache_duration = $duration;
        return $this;
    }

    /**
     * @return int
     */
    public function getCacheDuration(): int
    {
        return $this->cache_duration;
    }

    /**
     * @return string
     */
    public function getHashPrefix(): string
    {
        return $this->getTableName();
    }

    /**
     * @return string
     */
    public function getTotalHashPrefix(): string
    {
        return 'total_' . $this->getHashPrefix();
    }

    /**
     * Set hash_name property
     *
     * @param string $hash_name
     * @return CachebleRepositoryInterface
     */
    public function setHashName(string $hash_name): CachebleRepositoryInterface
    {
        $this->hash_name = $hash_name;
        return $this;
    }

    /**
     * Get hash name for cache.
     *
     * @param bool $refresh
     * @param bool $use_params
     * @param bool $save_to
     *
     * @return string
     */
    public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string
    {
        if ($this->hash_name && !$refresh) {
            return $this->hash_name;
        } else {
            $hash_name = $this->getHashPrefix();
            if ($use_params && $this->getFilters()) {
                $hash_name = $hash_name . Config::getIdDelimiter() . $this->getHashFromArray($this->getFilters());
            }

            $hash_name = trim($hash_name, '&');

            if ($save_to) {
                $this->hash_name = $hash_name;
            }
        }

        return $hash_name ?? '';
    }

    /**
     * Insert data to cache storage.
     *
     * @param mixed $data data
     * @param array $params
     *
     * @return $this
     * @throws Exception
     */
    public function setToCache($data, array $params = []): CachebleRepositoryInterface
    {
        if (!$this->getHashName()) {
            return $this;
        }

        $this->getDriver()
            ->set(
                $this->getHashName(),
                $data,
                $this->getCacheDuration(),
                $params
            );

        return $this;
    }

    /**
     * Get data from cache storage.
     *
     * @param bool $refresh
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function getFromCache(bool $refresh = false, array $params = [])
    {
        if (!$this->getHashName($refresh)) {
            return [];
        }
        return $this->getDriver()
                ->get($this->getHashName(), $params) ?? [];
    }

    /**
     * Delete data in cache storage.
     *
     * @param array $params
     *
     * @return CachebleRepositoryInterface
     * @throws Exception
     */
    public function deleteFromCache(array $params = []): CachebleRepositoryInterface
    {
        if (!$this->getHashName()) {
            return $this;
        }

        $this->getDriver()
            ->delete($this->getHashName(), $params);

        return $this;
    }

    /**
     * Get string hash from array.
     * Hash is using for caching.
     *
     * @param array $data deprecated
     * @param bool $hashToMd5
     *
     * @return string
     */
    public function getHashFromArray(array $data, bool $hashToMd5 = false): string
    {
        if ($data) {
            $hash = Hasher::hash($data);
        }

        return $hash ?? '';
    }
}
