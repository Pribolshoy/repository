<?php

namespace pribolshoy\repository;

use pribolshoy\repository\AbstractCachebleService;

/**
 * Class EnormousCachebleService
 *
 * Обертка над CommonEntityService предоставляющая функционал
 * для работы с сущностями имеющими более 1000 строк в таблице.
 *
 * В кеш отправляется также все содержимое таблицы,
 * но некоторые методы недоступны, т.к. загружать в память
 * тысячи сущностей для фильтрации слишком накладно.
 * Поэтому данный вид сервисов предназначен больше для вытаскивания
 * немногочисленных строк из кеша по ID.
 *
 * @package app\services
 */
abstract class EnormousCachebleService extends AbstractCachebleService
{
    protected int $max_init_iteration = 10;

    /**
     * How much times was ran method
     * initStorage() recursively
     * @var int
     */
    protected ?int $init_iteration = null;

    protected bool $is_fetching = false;

    protected string $hash_prefix = 'detail_';

    protected string $alias_postfix = '_alias';

    protected string $alias_attribute = '';

    protected array $cache_params = [
        'strategy' => 'getValue'
    ];

    /**
     * @return int
     */
    public function getMaxInitIteration(): int
    {
        return $this->max_init_iteration;
    }

    /**
     * @param int $init_iteration
     * @return $this
     */
    public function setInitIteration(?int $init_iteration): self
    {
        $this->init_iteration = $init_iteration;
        return $this;
    }

    /**
     * @return int
     */
    public function getInitIteration(): ?int
    {
        return $this->init_iteration;
    }

    /**
     * @param bool|null $is_fetching
     * @return EnormousCachebleService
     */
    public function setIsFetching(bool $is_fetching): self
    {
        $this->is_fetching = $is_fetching;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isFetching()
    {
        return $this->is_fetching ?? false;
    }

    /**
     * @param string $alias_postfix
     *
     * @return EnormousCachebleService
     */
    public function setAliasPostfix(string $alias_postfix): self
    {
        $this->alias_postfix = $alias_postfix;

        return $this;
    }

    /**
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
     * Get item value that will be used as key in alias cache.
     *
     * @param $item
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getItemAliasValue($item)
    {
        return $this->getItemAttribute($item, $this->getAliasAttribute());
    }

    /**
     * Get primary key from alias hash table.
     *
     * @param string $alias
     * @param AbstractCachebleRepository|null $repository
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getPrimaryKeyByAlias(string $alias, ?AbstractCachebleRepository $repository = null)
    {
        /** @var AbstractCachebleRepository $repository */
        if (!$repository) $repository = $this->getRepository();

        $fetch_from_repository = false;

        if ($this->isUseCache()) {
            $alias_hash = $this->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->getAliasPostfix()
                . ':' . $alias;

            $primaryKey = $repository
                ->setHashName($alias_hash)
                ->getFromCache();

            // if primary key not found - checks if cache exists
            if (!$primaryKey) {
                if ($repository->isCacheble()
                    && !$this->isCacheExists($repository)
                ) {
                    $this->cacheNotExistsEvent();
                    $fetch_from_repository = true;
                } else if (!$repository->isCacheble()) {
                    $fetch_from_repository = true;
                }
            }

        } else {
            $fetch_from_repository = true;
        }

        if ($fetch_from_repository) {
            // get primary key throw repository
            // because cache intiation may by sends to queue
            $items = $repository
                ->setParams([$this->getAliasAttribute() => $alias], true, true)
                ->search();

            if ($item = $items[0] ?? null) {
                $primaryKey = $this->getItemPrimaryKey($item);
            }
        }

        return $primaryKey;
    }

    /**
     * @param null $repository
     * @param bool $refresh_repository_cache
     *
     * @return $this|\pribolshoy\repository\AbstractCachebleService
     * @throws \Exception
     */
    public function initStorage($repository = null, $refresh_repository_cache = false)
    {
        $this->setItems([]);

        // null mean that it first run of this method
        if (is_null($this->getInitIteration())) {
            $this->setInitIteration(0);
            $fetched_items = 0;
        } else {
            $this->setInitIteration($this->getInitIteration()+1);
            $fetched_items = $this->getInitIteration() * $this->getFetchingStep();
        }

        /** @var $repository AbstractCachebleRepository */
        if (!$repository)
            $repository = $this->getRepository([
                'limit'     => $this->getFetchingStep(),
                'offset'    => $this->getInitIteration() * $this->getFetchingStep(),
            ]);

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

            $this->setIsFetching(($repository->getTotalCount() > ($fetched_items + count($items))));

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
     * @return bool
     *
     * @throws \Exception
     */
    protected function cacheNotExistsEvent(): bool
    {
        // call first time init storage event by parent
        if (parent::cacheNotExistsEvent()
            && $this->isFetching()
        ) {
            for ($i = 0; $i < $this->max_init_iteration; $i++) {
                $this->initStorageEvent();
                if (!$this->isFetching()) {
                    $this->setInitIteration(null);
                    break;
                }
            }
        } else {
            $this->setInitIteration(null);
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    protected function initStorageEvent(): bool
    {
        $this->initStorage(
            null,
            is_null($this->getInitIteration()) ? true : false
        );
        return true;
    }

    /**
     * Adding actions after initStorage().
     *
     * @param AbstractCachebleRepository $repository
     *
     * @return bool
     * @throws \Exception
     */
    protected function afterInitStorage(AbstractCachebleRepository $repository): bool
    {
        parent::afterInitStorage($repository);
        $this->initAliasCache($repository);
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
        if ($items = $this->getItems()) {
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
     * Get item by alias throw alias hash table.
     *
     * @param string $alias
     * @param array $attributes
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getByAlias(string $alias, array $attributes = [])
    {
        /** @var AbstractCachebleRepository $repository */
        $primaryKey = $this->getPrimaryKeyByAlias(
            $alias,
            $repository = $this->getRepository()
        );

        if ($primaryKey) {
            $fetch_from_repository = false;

            if ($this->isUseCache()) {
                $item = $repository
                    ->setHashName(
                        $this->getHashPrefix()
                        . $repository->getHashPrefix()
                        . ':' . $primaryKey
                    )
                    ->getFromCache();

                if (!$item) {
                    if ($repository->isCacheble()
                        && !$this->isCacheExists()
                    ) {
                        $this->cacheNotExistsEvent();
                        $fetch_from_repository = true;
                    } else if (!$repository->isCacheble()) {
                        $fetch_from_repository = true;
                    }
                }
            } else {
                $fetch_from_repository = true;
            }

            if ($fetch_from_repository) {
                // get primary key throw repository
                // because cache intiation may by sends to queue
                $items = $repository
                    ->setParams(['id' => $primaryKey], true, true)
                    ->search();

                if ($item = $items[0] ?? null) {
                    $item = $this->prepareItem($item);
                    $item = $this->filterByAttributes($item, $attributes);
                }
            }
        }

        return $item ?? [];
    }

    protected function filterByAttributes($item, array $attributes)
    {
        $result = [];

        if ($attributes) {
            foreach ($attributes as $name => $value) {
                if (is_string($value)) $value = [$value];

                $attribute = $this->getItemAttribute($item, $name);

                if (!$attribute
                    || !in_array($attribute, $value)
                ) {
                    continue;
                }

                $result = $item;
            }
        } else {
            $result = $item;
        }

        return $result;
    }

    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        throw new \Exception('Method getList() in EnormousCachebleService is not realized!');
    }

    public function getByExp(array $attributes)
    {
        throw new \Exception('Method getByExp() in EnormousCachebleService is not realized!');
    }

    public function getByMulti(array $attributes)
    {
        throw new \Exception('Method getByMulti() in EnormousCachebleService is not realized!');
    }

    public function getBy(array $attributes)
    {
        throw new \Exception('Method getBy() in EnormousCachebleService is not realized!');
    }

    public function getById(int $id, array $attributes = [])
    {
        $item = $this->getByIds([$id], $attributes);

        return $item[0] ?? [];
    }

    /**
     *
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [])
    {
        /** @var AbstractCachebleRepository $repository */
        $repository = $this->getRepository();
        $params = array_merge($this->cache_params, ['fields' => $ids]);

        $fetch_from_repository = false;
        
        if ($this->isUseCache()) {
            $items = $repository
                ->setHashName($this->getHashPrefix() . $repository->getHashPrefix())
                ->getFromCache(false, $params);

            // if cache not exists - do init storage for all items
            if (!$items) {
                if ($repository->isCacheble()
                    && !$this->isCacheExists($repository)
                ) {
                    $this->cacheNotExistsEvent();
                    $fetch_from_repository = true;
                } else if (!$repository->isCacheble()) {
                    $fetch_from_repository = true;
                }
            }
        } else {
            $fetch_from_repository = true;
        }

        if ($fetch_from_repository) {
            $items = $repository
                ->setParams(['ids' => $ids], true, true)
                ->search();
        }

        return $items ?? [];
    }

}
