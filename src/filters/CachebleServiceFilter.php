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
}

