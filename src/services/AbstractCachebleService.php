<?php

namespace pribolshoy\repository\services;

use Exception;
use pribolshoy\repository\Config;
use pribolshoy\repository\Logger;
use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\CachebleServiceFilter;
use pribolshoy\repository\filters\EnormousServiceFilter;
use pribolshoy\repository\interfaces\BaseServiceInterface;
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

    protected string $alias_postfix = ':alias';

    protected string $alias_attribute = '';

    protected string $caching_prefix = 'caching:';

    /**
     * Were items fetched from cache or not.
     *
     * @var bool
     */
    protected bool $is_from_cache = true;

    /**
     * Params for cache driver.
     * Разделены на 'get' (для выборки из кеша) и 'set' (для сохранения в кеш).
     *
     * @var array
     */
    public array $cache_params = [
        'get' => [
            'strategy' => 'hash'
        ],
        'set' => [
            'strategy' => 'hash'
        ]
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
    public function setUseCache(bool $use_cache): CachebleServiceInterface
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
    public function setUseAliasCache(bool $use_alias_cache): CachebleServiceInterface
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
     * @throws Exception
     */
    protected function getAliasStructure(bool $refresh = false): HashtableStructure
    {
        if (is_null($this->alias_item_structure) || $refresh) {
            $class = $this->alias_item_structure_class;

            if (!$class) {
                throw new ServiceException('Property alias_item_structure_class is not set');
            } elseif (!class_exists($class)) {
                throw new ServiceException(
                    'Item structure class not found: '
                    . $this->alias_item_structure_class ?? 'empty'
                );
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
     * @throws Exception
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
    public function updateHashtable(): BaseServiceInterface
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
     * @param string $name 'get' или 'set'
     * @param array $param
     *
     * @return object
     */
    public function addCacheParams(string $name, array $param): CachebleServiceInterface
    {
        if (!isset($this->cache_params[$name])) {
            $this->cache_params[$name] = [];
        }
        $this->cache_params[$name] = array_merge($this->cache_params[$name], $param);
        return $this;
    }

    /**
     * @param array $cache_params Должен содержать ключи 'get' и/или 'set'
     *
     * @return object
     */
    public function setCacheParams(array $cache_params): CachebleServiceInterface
    {
        // Поддержка обратной совместимости: если передан старый формат
        if (isset($cache_params['strategy']) && !isset($cache_params['get']) && !isset($cache_params['set'])) {
            $this->cache_params['get'] = ['strategy' => $cache_params['strategy']];
            if (isset($cache_params['fields'])) {
                $this->cache_params['get']['fields'] = $cache_params['fields'];
            }
        } else {
            $this->cache_params = array_merge($this->cache_params, $cache_params);
        }
        return $this;
    }

    /**
     * @param string $name 'get' или 'set', или пустая строка для получения всех параметров
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
     * @return bool default false
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
    public function setIsFromCache(bool $is_from_cache): CachebleServiceInterface
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
     * @param CachebleRepositoryInterface|null $repository
     *
     * @return mixed
     * @throws Exception
     */
    public function isCacheExists(?CachebleRepositoryInterface $repository = null): bool
    {
        /** @var CachebleRepositoryInterface $repository */
        if (!$repository) {
            $repository = $this->getRepository();
        }

        return (bool)$repository->setHashName(
            $this->caching_prefix
            . $this->getHashPrefix()
            . $repository->getHashPrefix()
        )->getFromCache(false, ['strategy' => 'string']);
    }

    /**
     * Event will fire when we should to initiate cache.
     * We can override this method in children, for example if
     * we want to initiate cache async in queue.
     *
     * @return bool
     *
     * @throws Exception
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
     * Preparing item after fetching from repository.
     * In this statement it will retrieved by service client.
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
    public function setAliasPostfix(string $alias_postfix): CachebleServiceInterface
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
     * @throws Exception
     */
    public function getAliasAttribute(): string
    {
        if (!$this->alias_attribute) {
            throw new Exception('Name of item attribute for alias is not set');
        }

        return $this->alias_attribute;
    }

    /**
     * Get primary key from alias hash table.
     *
     * @param string                          $alias
     * @param CachebleRepositoryInterface|null $repository
     *
     * @return mixed
     * @throws Exception
     */
    public function getPrimaryKeyByAlias(
        string $alias,
        ?CachebleRepositoryInterface $repository = null
    ) {
        /** @var CachebleServiceFilter $filter */
        $filter = $this->getFilter();

        return $filter
            ->getPrimaryKeyByAlias($alias, $repository) ?? null;
    }

    /**
     * Get item by alias throw hash table.
     *
     * @param string $alias
     * @param array  $attributes
     *
     * @return array|mixed
     * @throws Exception
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
     * @throws Exception
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false): CachebleServiceInterface
    {
        $this->setItems([]);

        /** @var CachebleRepositoryInterface $repository */
        if (!$repository) {
            $repository = $this->getRepository([
                'limit' => $this->getFetchingStep()
            ]);
        }

        $this->setIsFromCache(false);

        // if we need to refresh - delete cache anyway
        if ($this->isUseCache() && $refresh_repository_cache) {
            $this->clearStorage($repository);
        }

        // if rows were found - set it to items
        if ($items = $repository->search()) {
            // preparing
            foreach ($items as &$mutableItem) {
                $mutableItem = $this->prepareItem($mutableItem);
            }

            $this->setItems($this->sort($items));

            // if repos is cacheble - set items to cache
            if ($this->isUseCache() && $repository->isCacheble()) {
                $baseHashName = $this->getHashPrefix() . $repository->getHashPrefix();
                foreach ($items as $item) {
                    $hash_name = $baseHashName
                        . $this->getIdPostfix() . $this->getItemIdValue($item);

                    $repository
                        ->setHashName($hash_name)
                        ->setToCache($item, $this->getCacheParams('set'));
                }

                Logger::log('initStorage', $baseHashName, 'service', $items);
            }
        }

        $this->afterInitStorage($repository);

        return $this;
    }

    /**
     * Adding storage initiations of related cache.
     *
     * @param CachebleRepositoryInterface $repository
     *
     * @return bool
     * @throws Exception
     */
    protected function afterInitStorage(CachebleRepositoryInterface $repository): bool
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
     * @param CachebleRepositoryInterface $repository
     *
     * @return bool
     * @throws Exception
     */
    protected function initCachingFlag(CachebleRepositoryInterface $repository): bool
    {
        if ($this->isUseCache()) {
            $ttlToMinus = Config::getCacheTtlToMinus();
            $repository->setCacheDuration($repository->getCacheDuration() - $ttlToMinus)
                ->setHashName(
                    $this->caching_prefix
                    . $this->getHashPrefix()
                    . $repository->getHashPrefix()
                )->setToCache(1, ['strategy' => 'string'])
                ->setCacheDuration($repository->getCacheDuration() + $ttlToMinus);
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
     * @throws Exception
     */
    public function refreshItem(array $primaryKeyArray): bool
    {
        if (!$primaryKeyArray) {
            return true;
        }

        /** @var CachebleRepositoryInterface $repository */
        $repository = $this->getRepository(
            array_merge($primaryKeyArray, ['limit' => 1,])
        );

        if (!$this->isUseCache() || !$repository->isCacheble()) {
            return true;
        }

        if ($items = $repository->search()) {
            $item = $items[0];

            $hash_name = $this->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->getIdPostfix() . $this->getItemIdValue($item);

            $item = $this->prepareItem($item);

            // if item is in storage - it'll rewrite, if not - saved
            $repository
                ->setHashName($hash_name)
                ->setToCache($item, $this->getCacheParams('set'));

            Logger::log('refreshItem', $hash_name, 'service', $item);

            $this->addItem($item);
        } else {
            $primaryKey = implode('', $primaryKeyArray);
            $this->deleteItem($primaryKey);
        }

        $this->afterRefreshItem($primaryKeyArray);

        return true;
    }

    /**
     * Event after item refresh.
     * Can be overridden in child classes.
     *
     * @param array $primaryKeyArray Primary key array
     * @return void
     */
    public function afterRefreshItem(array $primaryKeyArray): void
    {
    }


    /**
     * Delete all entity cache from storage
     *
     * @param CachebleRepositoryInterface|null $repository
     * @param array $params
     *
     * @return bool
     * @throws Exception
     */
    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = []): bool
    {
        /** @var CachebleRepositoryInterface $repository */
        if (!$repository) {
            $repository = $this->getRepository($params);
        }

        // delete items
        $itemsHashName = $this->getHashPrefix() . $repository->getHashPrefix();

        // TODO: подумать как исправить костыль
        if ($this->getCacheParams('get')['strategy'] == 'string') {
            $itemsHashName .= '*';
        }
        $repository
            ->setHashName($itemsHashName)
            ->deleteFromCache();

        Logger::log('clearStorage', $itemsHashName, 'service');

        // delete caching flag
        $cachingFlagHashName = $this->caching_prefix
            . $this->getHashPrefix()
            . $repository->getHashPrefix();
        $repository
            ->setHashName($cachingFlagHashName)
            ->deleteFromCache();

        Logger::log('clearStorage', $cachingFlagHashName, 'service');

        $this->afterStorageClear($repository);

        return true;
    }

    /**
     * Any additional events after storage clear.
     *
     * @param CachebleRepositoryInterface $repository
     *
     * @return bool
     * @throws Exception
     */
    protected function afterStorageClear(CachebleRepositoryInterface $repository): bool
    {
        if ($this->useAliasCache()) {
            // always clear alias cache
            $aliasHashName = $this->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->getAliasPostfix();
            $repository
                ->setHashName($aliasHashName)
                ->deleteFromCache();

            Logger::log('clearStorage', $aliasHashName, 'service');
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
     * @throws Exception
     */
    public function deleteItem(string $primaryKey): bool
    {
        /** @var CachebleRepositoryInterface $repository */
        $repository = $this->getRepository();

        // delete item
        $hash_name = $this->getHashPrefix()
            . $repository->getHashPrefix()
            . $this->getIdPostfix() . $primaryKey;
        $repository
            ->setHashName($hash_name)
            ->deleteFromCache();

        Logger::log('deleteItem', $hash_name, 'service');

        $this->afterDeleteItem($repository);

        return true;
    }

    /**
     * Any additional events after item deleting.
     *
     * @param CachebleRepositoryInterface $repository
     *
     * @return bool
     * @throws Exception
     */
    protected function afterDeleteItem(CachebleRepositoryInterface $repository): bool
    {
        return true;
    }

    /**
     * Init to cache alias hash table, which we will use
     * for searching item primary key.
     *
     * @param CachebleRepositoryInterface $repository
     *
     * @return bool
     * @throws Exception
     */
    protected function initAliasCache(CachebleRepositoryInterface $repository): bool
    {
        if (
            $this->useAliasCache()
            && $items = $this->getItems()
        ) {
            // initiation of alias cache
            if ($this->isUseCache() && $repository->isCacheble()) {
                foreach ($items as $item) {
                    $hash_name = $this->getHashPrefix()
                        . $repository->getHashPrefix()
                        . $this->getAliasPostfix()
                        . $this->getIdPostfix() . $this->getItemAliasValue($item);

                    $repository
                        ->setHashName($hash_name)
                        ->setToCache($this->getItemPrimaryKey($item), $this->getCacheParams('set'));
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
     * @throws Exception
     */
    protected function getItemAliasValue($item)
    {
        if (!$this->useAliasCache()) {
            throw new ServiceException("In this service alias cache isn't active");
        }

        return $this->getItemAttribute($item, $this->getAliasAttribute());
    }

    /**
     * Getter of id_postfix
     * Определяет поствикс на основе стратегии кеширования из cache_params['get'].
     * Обращается к драйверу кеша для определения правильного делимитера.
     * Если драйвер поддерживает метод getIdPostfixByStrategy, использует его,
     * иначе возвращает делимитер по умолчанию из конфигурации.
     *
     * @return string
     * @throws ServiceException
     */
    public function getIdPostfix(): string
    {
        // Получаем параметры для чтения из кеша
        $cacheParamsGet = $this->getCacheParams('get');

        // Пытаемся получить делимитер от драйвера, если он поддерживает эту функциональность
        if (
            $driver = $this->getRepository()->getDriver()
            and method_exists($driver, 'getIdPostfixByStrategy')
        ) {
            return $driver->getIdPostfixByStrategy($cacheParamsGet);
        }

        // Если драйвер не поддерживает метод, используем делимитер по умолчанию
        return Config::getIdDelimiter();
    }

    /**
     * Get item ID value that will be used as key in cache.
     * По умолчанию возвращает первичный ключ сущности.
     *
     * @param $item
     *
     * @return mixed
     */
    public function getItemIdValue($item)
    {
        return $this->getItemPrimaryKey($item);
    }
}
