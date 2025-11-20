<?php

namespace pribolshoy\repository\filters;

use Exception;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;

/**
 * Class CachebleServiceFilter
 *
 * @package app\repositories
 */
class CachebleServiceFilter extends ServiceFilter
{
    /**
     * Get all elements from cache or storage.
     *
     * @param array $params
     * @param bool $cache_to
     *
     * @return array|null
     * @throws Exception
     */
    public function getList(array $params = ['limit' => 500], bool $cache_to = true): ?array
    {
        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        if ($items = $service->getItems()) {
            return $service->getItems();
        }

        /** @var CachebleRepositoryInterface $repository */
        $repository = $service->getRepository($params);

        $repository
            ->setActiveCache($cache_to)
            ->setHashName(
                $service->getHashPrefix()
                . $repository->getHashName(true, false)
            );

        $service->setIsFromCache(true);

        $items = [];

        if ($service->isUseCache()) {
            $items = $repository->getFromCache(false, $service->getCacheParams('get'));

            $service->setItems($service->sort($items));
        }

        if (!$items) {
            $service->initStorageEvent();
            $service->setIsFromCache(false);
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
     * @throws Exception
     */
    public function getByAlias(string $alias, array $attributes = [])
    {
        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        /** @var CachebleRepositoryInterface $repository */
        $primaryKey = $this->getPrimaryKeyByAlias(
            $alias,
            $repository = $service->getRepository()
        );

        if ($primaryKey) {
            $fetch_from_repository = false;

            if ($service->isUseCache()) {
                $item = $repository
                    ->setHashName(
                        $service->getHashPrefix()
                        . $repository->getHashPrefix()
                        . $service->getIdPostfix() . $primaryKey
                    )
                    ->getFromCache();

                if (!$item) {
                    if (
                        $repository->isCacheble()
                        && !$service->isCacheExists()
                    ) {
                        $service->initStorageEvent();
                        $fetch_from_repository = true;
                    } elseif (!$repository->isCacheble()) {
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
                        ['id' => $primaryKey], // TODO: replace id by primaryKey
                        true,
                        true
                    )
                    ->search();

                if ($item = $items[0] ?? null) {
                    $item = $service->prepareItem($item);
                    if (!$this->filterByAttributes($item, $attributes)) {
                        $item = null;
                    }
                }
            }
        }

        return $item ?? [];
    }

    /**
     * Get primary key from alias hash table.
     *
     * @param string $alias
     * @param CachebleRepositoryInterface|null $repository
     *
     * @return mixed
     * @throws Exception
     */
    public function getPrimaryKeyByAlias(string $alias, ?CachebleRepositoryInterface $repository = null)
    {
        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        /** @var CachebleRepositoryInterface $repository */
        if (!$repository) {
            $repository = $service->getRepository();
        }

        $fetch_from_repository = false;

        if (
            $service->getItems()
            && !is_null($result = $service->getByAliasStructure($alias))
        ) {
            $primaryKey = $result;
        } elseif ($service->isUseCache()) {
            $alias_hash = $service->getHashPrefix()
                . $repository->getHashPrefix()
                . $service->getAliasPostfix()
                . $service->getIdPostfix() . $alias;

            $primaryKey = $repository
                ->setHashName($alias_hash)
                ->getFromCache();

            // if primary key not found - checks if cache exists
            if (!$primaryKey) {
                if (
                    $repository->isCacheble()
                    && !$service->isCacheExists($repository)
                ) {
                    $service->initStorageEvent();
                    $fetch_from_repository = true;
                } elseif (!$repository->isCacheble()) {
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
                    [$service->getAliasAttribute() => $alias],
                    true,
                    true
                )->search();

            if ($item = $items[0] ?? null) {
                $primaryKey = $service->getItemPrimaryKey($item);
            }
        }

        return $primaryKey ?? null;
    }
}
