<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\EnormousServiceInterface;

/**
 * Class EnormousServiceFilter
 *
 */
class EnormousServiceFilter extends CachebleServiceFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param int $id
     * @param array $attributes
     * @param bool $cacheOnly
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getById(int $id, array $attributes = [], bool $cacheOnly = false)
    {
        $item = $this->getByIds([$id], $attributes, $cacheOnly);

        return $item[0] ?? [];
    }

    /**
     * @param array $ids
     * @param array $attributes not used
     * @param bool $cacheOnly
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [], bool $cacheOnly = false): array
    {
        if (!$ids) {
            return [];
        }

        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        /** @var CachebleRepositoryInterface $repository */
        $repository = $service->getRepository();
        $params = array_merge($service->getCacheParams('get'), ['fields' => $ids]);

        $fetch_from_repository = false;

        $repository->setHashName($service->getHashPrefix() . $repository->getHashPrefix());

        if ($service->isUseCache() && $service->isCacheExists($repository, $params)) {
            $items = $repository->getFromCache(false, $params);

            if (!$items || !$repository->isCacheble()) {
                $fetch_from_repository = true;
            }
        } else {
            if (
                !$cacheOnly && $service->isUseCache()
                && !$service->isCacheExists($repository) && $repository->isCacheble()
            ) {
                // if cache not exists - do init storage for all items
                $service->initStorageEvent();
            }
            $fetch_from_repository = true;
        }

        if ($fetch_from_repository && !$cacheOnly) {
            $items = $repository
                ->setParams(
                    ['ids' => $ids],
                    true,
                    true
                )->search();

            foreach ($items as &$item) {
                $item = $service->prepareItem($item);
            }
        }

        if (($items ?? null) && !$fetch_from_repository) {
            $service->setIsFromCache(true);
        }

        return $items ?? [];
    }

    /**
     * @override
     *
     * @param string $alias
     * @param array $attributes
     * @param bool $cacheOnly
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getByAlias(string $alias, array $attributes = [], bool $cacheOnly = false)
    {
        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        if ($item = parent::getByAlias($alias, $attributes, $cacheOnly)) {
            if ($item) {
                $service->addItem($item, false);
            }
        }

        return $item ?? [];
    }
}
