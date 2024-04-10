<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;

/**
 * Class PaginatedServiceFilter
 *
 * @package app\repositories
 */
class PaginatedServiceFilter extends AbstractFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        /** @var $service CachebleServiceInterface */
        /** @var $repository CachebleRepositoryInterface */
        $service = $this->getService();
        $repository = $service->getRepository($params);

        $repository->setActiveCache($cache_to);

        $hash = $service->getHashPrefix() . $repository->getHashName();

        $pages = null;
        $ids = [];
        if ($service->isUseCache())
            $ids = $repository
                ->setHashName($hash)
                ->getFromCache(false, $service->cache_params);

        // if no data in cache - try to fetch it from repository
        if (!$ids && $items = $repository->search()) {
            $ids = $service->collectItemsPrimaryKeys($items);

            // get pagination from repository
            $service->setPages($pages = $repository->getPages());

            if ($repository->isCacheble()) {
                // ids
                $repository
                    ->setHashName($hash)
                    ->setToCache($ids);

                if ($pages) {
                    // pagination results
                    $repository
                        ->setHashName($service->pagination_prefix . $repository->getHashName())
                        ->setToCache($repository->getPages());
                }
            }
        }

        if ($ids && ($items = $service->getByIds($ids))) {
            $service->setItems($items);

            if (is_null($pages)) {
                $pages = $repository
                    ->setHashName($service->pagination_prefix . $repository->getHashName())
                    ->getFromCache(false, $service->cache_params);
            }

            if ($pages) {
                $service->setPages($pages);
            }
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
    public function getByIds(array $ids, array $attributes = [])
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }
}

