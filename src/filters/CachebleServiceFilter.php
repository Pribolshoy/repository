<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractCachebleService;

/**
 * Class CachebleServiceFilter
 *
 * @package app\repositories
 */
class CachebleServiceFilter extends ServiceFilter
{
    /**
     * Get all elements from cache.
     *
     * @param array $params
     * @param bool $cache_to
     *
     * @return array|null
     * @throws \Exception
     */
    public function getList(array $params = ['limit' => 500], bool $cache_to = true) : ?array
    {
        /** @var $service AbstractCachebleService */
        $service = $this->getService();

        if ($service->getItems()) return $service->getItems();

        /** @var $repository AbstractCachebleRepository */
        $repository = $service->getRepository($params);

        $repository
            ->setActiveCache($cache_to)
            ->getHashName(true, false);

        $service->setIsFromCache(true);

        $items = [];
        // если в сервисе разрешено использования кеширования - пытаемся получить из кеша
        if ($service->isUseCache())
            $items = $repository->getFromCache(false, $service->cache_params);

        if (!$items) {
            $service->initStorageEvent();
            $service->setIsFromCache(false);
            $items = $repository->search();
        }

        if ($items) {
            $service->setItems($service->sort($items));
            $service->updateHashtable();
        }

        return $service->getItems() ?? [];
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
            $repository = $this->getService()->getRepository()
        );

        if ($primaryKey) {
            $fetch_from_repository = false;

            if ($this->getService()->isUseCache()) {
                $item = $repository
                    ->setHashName(
                        $this->getService()->getHashPrefix()
                        . $repository->getHashPrefix()
                        . ':' . $primaryKey
                    )
                    ->getFromCache();

                if (!$item) {
                    if ($repository->isCacheble()
                        && !$this->getService()->isCacheExists()
                    ) {
                        $this->getService()->initStorageEvent();
                        $fetch_from_repository = true;
                    } else if (!$repository->isCacheble()) {
                        $fetch_from_repository = true;
                    }
                }
            } else {
                $fetch_from_repository = true;
            }

            if ($fetch_from_repository) {
                // get primary key by repository
                // because cache intiation may by sends to queue
                $items = $repository
                    ->setParams(
                        ['id' => $primaryKey],
                        true,
                        true
                    )
                    ->search();

                if ($item = $items[0] ?? null) {
                    $item = $this->getService()->prepareItem($item);
                    $item = $this->filterByAttributes($item, $attributes);
                }
            }
        }

        return $item ?? [];
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
    public function getPrimaryKeyByAlias(string $alias, ?AbstractCachebleRepository $repository = null)
    {
        /** @var AbstractCachebleRepository $repository */
        if (!$repository) $repository = $this->getService()->getRepository();

        $fetch_from_repository = false;

        if ($this->getService()->isUseCache()) {
            $alias_hash = $this->getService()->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->getService()->getAliasPostfix()
                . ':' . $alias;

            $primaryKey = $repository
                ->setHashName($alias_hash)
                ->getFromCache();

            // if primary key not found - checks if cache exists
            if (!$primaryKey) {
                if ($repository->isCacheble()
                    && !$this->getService()->isCacheExists($repository)
                ) {
                    $this->getService()->initStorageEvent();
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
                ->setParams(
                    [$this->getService()->getAliasAttribute() => $alias],
                    true,
                    true
                )
                ->search();

            if ($item = $items[0] ?? null) {
                $primaryKey = $this->getService()->getItemPrimaryKey($item);
            }
        }

        return $primaryKey ?? null;
    }
}

