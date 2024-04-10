<?php

namespace pribolshoy\repository;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\CachebleServiceFilter;
use pribolshoy\repository\filters\EnormousServiceFilter;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\HashtableStructure;
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
     * Should we try to grt items from cache.
     *
     * @var bool
     */
    protected bool $use_cache = true;

    /**
     * Should we make alias table in cache storage.
     *
     * @var bool
     */
    protected bool $use_alias_cache = false;

    protected ?HashtableStructure $alias_item_structure = null;

    protected string $alias_item_structure_class = HashtableStructure::class;

    /**
     * Prefix for key of hash table in cache storage.
     *
     * @var string
     */
    protected string $hash_prefix = '';

    protected string $alias_postfix = '_alias';

    protected string $alias_attribute = '';

    protected string $caching_postfix = '_caching';

    /**
     * Were items fetched from cache or not.
     *
     * @var bool
     */
    protected bool $is_from_cache = true;

    /**
     * Params for cache driver.
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
     * @return bool
     */
    public function useAliasCache(): bool
    {
        return $this->use_alias_cache;
    }

    /**
     * @param bool $use_alias_cache
     *
     * @return $this
     */
    public function setUseAliasCache(bool $use_alias_cache)
    {
        $this->use_alias_cache = $use_alias_cache;
        return $this;
    }

    /**
     * Get Alias hashtable structure object.
     *
     * @param bool $refresh
     *
     * @return StructureInterface|HashtableStructure
     * @throws ServiceException
     * @throws \Exception
     */
    protected function getAliasStructure(bool $refresh = false):HashtableStructure
    {
        if (is_null($this->alias_item_structure) || $refresh) {
            $class = $this->alias_item_structure_class;

            if (!$class) {
                throw new ServiceException('Property alias_item_structure_class is not set');
            } else if (!class_exists($class)) {
                throw new ServiceException('Item structure class not found: ' . $this->alias_item_structure_class ?? 'empty');
            }

            $this->alias_item_structure = new $class($this);
            $this->alias_item_structure->setKeyName($this->getAliasAttribute());
            $this->alias_item_structure->setCursorKeys($this->primaryKeys);
        }

        return $this->alias_item_structure;
    }

    /**
     * Get item primary key by alias structure.
     *
     * @param string $value value of alias attribute
     *
     * @return string|int|null
     * @throws exceptions\ServiceException
     * @throws \Exception
     */
    public function getByAliasStructure($value)
    {
        $structure = $this->getAliasStructure();

        // get key for item/items by hashtable
        return $structure
            ->getByKey($value) ?? null;
    }

    /**
     * @override
     *
     * @return object
     *
     * @throws ServiceException
     */
    public function updateHashtable() :object
    {
        parent::updateHashtable();

        if ($this->useAliasCache()) {
            $this->getAliasStructure()
                ->setItems($this->getItems() ?? []);
        }

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
     *
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
     * TODO: make protected
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
     * TODO: make protected
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
     * We can override this method in children, for example if
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
     *
     * @return mixed
     */
    public function prepareItem($item)
    {
        return $item;
    }


    /**
     * Setter of alias_postfix
     *
     * @param string $alias_postfix
     *
     * @return $this
     */
    public function setAliasPostfix(string $alias_postfix): object
    {
        $this->alias_postfix = $alias_postfix;

        return $this;
    }

    /**
     * Getter of alias_postfix
     *
     * @return string
     */
    public function getAliasPostfix(): string
    {
        return $this->alias_postfix;
    }

    /**
     * Get item attribute name which will used as key
     * in alias hash table.
     *
     * @return string
     * @throws \Exception
     */
    public function getAliasAttribute(): string
    {
        if (!$this->alias_attribute) {
            throw new \Exception('Name of item attribute for alias is not set');
        }

        return $this->alias_attribute;
    }

    /**
     * Get primary key from alias hash table.
     *
     * @param string                          $alias
     * @param AbstractCachebleRepository|null $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPrimaryKeyByAlias(
        string $alias,
        ?AbstractCachebleRepository $repository = null
    ) {
        /** @var CachebleServiceFilter $filter */
        $filter = $this->getFilter();

        return $filter
                ->getPrimaryKeyByAlias($alias, $repository) ?? null;
    }

    /**
     * Get item by alias throw alias hash table.
     *
     * @param string $alias
     * @param array  $attributes
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getByAlias(string $alias, array $attributes = [])
    {
        /** @var CachebleServiceFilter|EnormousServiceFilter $filter */
        $filter = $this->getFilter();
        return $filter
                ->getByAlias($alias, $attributes) ?? null;
    }

    /**
     * Insert all entity rows to cache repository by cache driver
     * and populate items property.
     *
     * @param RepositoryInterface|null $repository
     * @param bool $refresh_repository_cache
     *
     * @return $this
     * @throws \Exception
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false)
    {
        $this->setItems([]);

        /** @var $repository AbstractCachebleRepository */
        if (!$repository) $repository = $this->getRepository(['limit' => $this->getFetchingStep()]);

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
        $this->initAliasCache($repository);
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
     * Refresh item by params throw cache driver
     * and in $items.
     *
     * @param array $primaryKeyArray
     *
     * @return bool
     * @throws exceptions\ServiceException
     * @throws \Exception
     */
    public function refreshItem(array $primaryKeyArray)
    {
        if (!$primaryKeyArray) return true;

        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository(
            array_merge($primaryKeyArray, ['limit' => 1,])
        );

        if (!$repository->isCacheble()) return true;

        if ($items = $repository->search()) {
            $item = $items[0];

            $hash_name = $this->getHashPrefix()
                . $repository->getHashPrefix()
                . ':' . $this->getItemPrimaryKey($item);

            // if item is in storage - it'll rewritten, if not - saved
            $repository
                ->setHashName($hash_name)
                ->setToCache($this->prepareItem($item));

            $this->addItem($item);
        } else {
            $primaryKey = implode('', $primaryKeyArray);
            $this->deleteItem($primaryKey);
        }

        return true;
    }

    /**
     * Delete all entity cache from storage
     *
     * @param CachebleRepositoryInterface|null $repository
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = [])
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
        if ($this->useAliasCache()) {
            // always clear alias cache
            $repository
                ->setHashName(
                    $this->getHashPrefix()
                    . $repository->getHashPrefix()
                    . $this->getAliasPostfix()
                )
                ->deleteFromCache();
        }

        return true;
    }

    /**
     * Delete item from storage by primary key
     *
     * @param string $primaryKey
     *
     * @return bool
     * @throws exceptions\ServiceException
     * @throws \Exception
     */
    public function deleteItem(string $primaryKey)
    {
        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository();

        // delete item
        $repository
            ->setHashName(
                $this->getHashPrefix()
                . $repository->getHashPrefix()
                . ':' . $primaryKey
            )
            ->deleteFromCache();

        $this->afterDeleteItem($repository);

        return true;
    }

    /**
     * Any additional events after item deleting.
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function afterDeleteItem(AbstractCachebleRepository $repository): bool
    {
        return true;
    }

    /**
     * Init to cache alias hash table, which we will use
     * for searching item primary key.
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function initAliasCache(AbstractCachebleRepository $repository): bool
    {
        if ($this->useAliasCache()
            && $items = $this->getItems()
        ) {
            // initiation of alias cache
            if ($repository->isCacheble()) {
                foreach ($items as $item) {
                    $hash_name = $this->getHashPrefix()
                        . $repository->getHashPrefix()
                        . $this->getAliasPostfix()
                        . ':' . $this->getItemAliasValue($item);

                    $repository
                        ->setHashName($hash_name)
                        ->setToCache($this->getItemPrimaryKey($item));
                }
            }
            $items = null;
        }

        return true;
    }

    /**
     * Get item value that will be used as key in alias cache.
     *
     * @param $item
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getItemAliasValue($item)
    {
        if (!$this->useAliasCache()) {
            throw new ServiceException("In this service alias cache isn't active");
        }

        return $this->getItemAttribute($item, $this->getAliasAttribute());
    }
}

