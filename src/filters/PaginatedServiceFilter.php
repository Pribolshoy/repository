<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\PaginatedCachebleServiceInterface;

/**
 * Class PaginatedServiceFilter
 *
 * @package app\repositories
 */
class PaginatedServiceFilter extends AbstractFilter
{

    /**
     * @param array $params
     * @param bool $cache_to
     *
     * @return array|null
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        /** @var $service PaginatedCachebleServiceInterface */
        /** @var $repository CachebleRepositoryInterface */
        $service = $this->getService();
        $repository = $service->getRepository($params);

        $repository->setActiveCache($cache_to);

        $hash = $service->getHashPrefix() . $repository->getHashName();

        $pages = null;
        $ids = [];
        if ($service->isUseCache()) {
            $ids = $repository
                ->setHashName($hash)
                ->getFromCache(false, $service->getCacheParams('get'));
        }

        // if no data in cache - try to fetch it from repository
        if (!$ids && $items = $repository->search()) {
            $ids = $service->collectItemsPrimaryKeys($items);

            // get pagination from repository
            $service->setPages($pages = $repository->getPages());

            if ($service->isUseCache() && $repository->isCacheble()) {
                // ids
                $repository
                    ->setHashName($hash)
                    ->setToCache($ids, $service->getCacheParams('set'));

                // pagination results
                $repository
                    ->setHashName($service->getPaginationHashPrefix() . $repository->getHashName())
                    ->setToCache($repository->getPages(), $service->getCacheParams('set'));
            }
        }

        if ($ids && ($items = $service->getByIds($ids))) {
            $service->setItems($items);

            if (is_null($pages) && $service->isUseCache()) {
                $pages = $repository
                    ->setHashName($service->getPaginationHashPrefix() . $repository->getHashName())
                    ->getFromCache(false, $service->getCacheParams('get'));
            }
        }

        if (is_null($pages)) {
            $service->setPages($repository->getPages());
        }

        return $service->getItems();
    }

    /**
     * Filter for paginated service can't get items by ids.
     * To get items we need use other service type.
     *
     * @param array $ids
     * @param array $attributes
     *
     * @return array|void
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = []): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }
}

