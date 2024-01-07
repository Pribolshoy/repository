<?php

namespace pribolshoy\repository;

use pribolshoy\repository\filters\CachebleServiceFilter;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\traits\CatalogTrait;

/**
 * Class AbstractCachebleService
 *
 * Abstract class for realization of service object
 * by which we can using repositories.
 *
 * @package app\repositories
 */
abstract class AbstractCachebleService extends AbstractService implements CachebleServiceInterface
{
    use CatalogTrait;

    protected string $filter_class = CachebleServiceFilter::class;

    /**
     * Was initStorage() fired yet on this call.
     * @var bool
     */
    protected bool $init_storage_fired = false;


    /**
     * Можно ли пытаться получить элементы из
     * кеша.
     *
     * @var bool
     */
    protected bool $use_cache = true;

    /**
     * Префикс к хешу кеширования.
     * Нужно для возможности менять его.
     *
     * @var string
     */
    protected string $hash_prefix = '';


    protected string $caching_postfix = '_caching';

    /**
     * Является ли результат выбранным из кеша
     *
     * @var bool
     */
    protected bool $is_from_cache = true;

    /**
     * Параметры передающиеся в дравер кеша при выборке
     *
     * @var array
     */
    public array $cache_params = [
        'strategy' => 'getAllHash'
    ];

    /**
     * Step of fetching rows by repository while init storage
     *
     * @var int
     */
    protected int $fetching_step = 1000;

    /**
     * @return bool
     */
    public function isUseCache(): bool
    {
        return $this->use_cache;
    }

    /**
     * @param bool $use_cache
     *
     * @return $this
     */
    public function setUseCache(bool $use_cache)
    {
        $this->use_cache = $use_cache;
        return $this;
    }

    /**
     * @param string $hash_prefix
     *
     * @return $this
     */
    public function setHashPrefix(string $hash_prefix): self
    {
        $this->hash_prefix = $hash_prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashPrefix(): string
    {
        return $this->hash_prefix;
    }

    /**
     * @param string $name
     *
     * @param array $param
     *
     * @return object
     */
    public function addCacheParams(string $name, array $param): object
    {
        $this->cache_params[$name] = $param;
        return $this;
    }

    /**
     * @param array $cache_params
     *
     * @return object
     */
    public function setCacheParams(array $cache_params): object
    {
        $this->cache_params = $cache_params;
        return $this;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getCacheParams(string $name = ''): array
    {
        if ($name) {
            return $this->cache_params[$name] ?? [];
        }

        return $this->cache_params;
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return $this->is_from_cache;
    }

    /**
     * @param bool $is_from_cache
     *
     * @return $this
     */
    public function setIsFromCache(bool $is_from_cache)
    {
        $this->is_from_cache = $is_from_cache;
        return $this;
    }

    /**
     * @return int
     */
    public function getFetchingStep(): int
    {
        return $this->fetching_step;
    }

    /**
     * @param int $fetching_step
     *
     * @return $this
     */
    public function setFetchingStep(int $fetching_step): self
    {
        $this->fetching_step = $fetching_step;
        return $this;
    }

    /**
     * Is cache exists for this repository.
     * It checks by look at existing of caching flag.
     *
     * @param AbstractCachebleRepository|null $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function isCacheExists(?AbstractCachebleRepository $repository = null)
    {
        /** @var AbstractCachebleRepository $repository */
        if (!$repository) $repository = $this->getRepository();

        return $repository
            ->setHashName(
                $this->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->caching_postfix
            )
            ->getFromCache() ? true : false;
    }

    /**
     * Event will fired when we should to initiate cache.
     * We can owerride this method in children, for example if
     * we want to initiate cache async in queue.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function initStorageEvent(): bool
    {
        if ($this->wasInitStorageFired()) {
            return false;
        }

        $this->initStorageFired();
        $this->initStorage(null, true);

        return true;
    }

    /**
     * @return AbstractCachebleService
     */
    protected function initStorageFired(): self
    {
        $this->init_storage_fired = true;
        return $this;
    }

    /**
     * Has the initialization of the storage process
     * for this object already been called.
     *
     * @return bool
     */
    protected function wasInitStorageFired(): bool
    {
        return $this->init_storage_fired;
    }

    /**
     * Preparing item before caching.
     * For example in this method we can create
     * DTO or Aggregation from item.
     *
     * @param $item
     * @return mixed
     */
    public function prepareItem($item)
    {
        return $item;
    }

    /**
     * Insert all entity rows to cache repository by cache driver
     * and populate items property.
     *
     * @param null $repository
     * @param bool $refresh_repository_cache
     *
     * @return $this
     * @throws \Exception
     */
    public function initStorage($repository = null, $refresh_repository_cache = false)
    {
        $this->setItems([]);

        $class = $this->getRepositoryClass();
        /** @var $repository AbstractCachebleRepository */
        if (!$repository) $repository = new $class(['limit' => $this->getFetchingStep()]);

        $this->setIsFromCache(false);

        // if we need to refresh - delete cache anyway
        if ($refresh_repository_cache)
            $this->clearStorage($repository);

        // if rows were found - set it to items
        if ($items = $repository->search()) {
            // preparing
            foreach ($items as &$item) {
                $item = $this->prepareItem($item);
            }

            $this->setItems($items);

            // if repos is cacheble - set items to cache
            if ($repository->isCacheble()) {
                foreach ($items as $item) {
                    $hash_name = $this->getHashPrefix()
                        . $repository->getHashPrefix()
                        . ':' . $this->getItemPrimaryKey($item);

                    $repository
                        ->setHashName($hash_name)
                        ->setToCache($item);
                }
            }
        }

        $this->afterInitStorage($repository);

        return $this;
    }

    /**
     * Adding storage initiations of related cache.
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function afterInitStorage(AbstractCachebleRepository $repository): bool
    {
        $this->initCachingFlag($repository);
        return true;
    }

    /**
     * Init cache flag, which says that cache for this
     * repository has already been initiated and don't need
     * to call initStorage().
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function initCachingFlag(AbstractCachebleRepository $repository): bool
    {
        if ($this->getItems()) {
            $repository
                ->setCacheDuration($repository->getCacheDuration() - 600)
                ->setHashName(
                    $this->getHashPrefix()
                    . $repository->getHashPrefix()
                    . $this->caching_postfix
                )
                ->setToCache(1)
                ->setCacheDuration($repository->getCacheDuration() + 600);
        }

        return true;
    }

    /**
     * Delete all entity cache from storage
     *
     * @param null $repository
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function clearStorage($repository = null, array $params = [])
    {
        /** @var $repository AbstractCachebleRepository */
        if (!$repository)
            $repository = $this->getRepository($params);

        // delete items
        $repository
            ->setHashName(
                $this->getHashPrefix()
                . $repository->getHashPrefix()
            )
            ->deleteFromCache();

        // delete caching flag
        $repository
            ->setHashName(
                $this->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->caching_postfix
            )
            ->deleteFromCache();

        $this->afterStorageClear($repository);

        return true;
    }

    /**
     * Any additional events after storage clear.
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function afterStorageClear(AbstractCachebleRepository $repository): bool
    {
        return true;
    }

    /**
     * Refresh item by params throw cache driver
     * and in static::items
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function refreshItem(array $params)
    {
        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository(array_merge($params, ['limit' => 1,]));

        if (($items = $repository->search())
            && $repository->isCacheble()
        ) {
            $item = $items[0];

            $hash_name = $this->getHashPrefix()
                . $repository->getHashPrefix()
                . ':' . $this->getItemPrimaryKey($item);
            // если объект уже есть - перепишется, если нет - сохранится
            $repository
                ->setHashName($hash_name)
                ->setToCache($this->prepareItem($item));

            // если элементы уже сохранены в данном объекте сервисе
            // то обновляем/вставляем в нем наш элемент
            if ($this->getItems()) $this->addItem($item);
        } else {
            // if item was not found - refresh all storage
            $this->clearStorage();
        }

        return true;
    }
}

