<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractCachebleService;
use pribolshoy\repository\AbstractService;

/**
 * Class PaginatedServiceFilter
 *
 * @package app\repositories
 */
class PaginatedServiceFilter extends AbstractFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        /** @var $service AbstractCachebleService */
        /** @var $repository AbstractCachebleRepository */
        $service = $this->getService();
        $repository = $service->getRepository($params);



        // устанавливаем разрешение использованя кеширования репозиторию
        $repository->setActiveCache($cache_to);

        $hash = $service->getHashPrefix() . $repository->getHashName();

        // если в сервисе разрешено использования кеширования - пытаемся получить из кеша
        $ids = [];
        if ($service->isUseCache())
            $ids = $repository
                ->setHashName($hash)
                ->getFromCache(false, $service->cache_params);

        // if no data in cache - try to fetch it from repository
        if (!$ids && $items = $repository->search()) {
            $ids = $service->collectItemsPrimaryKeys($items);

            // get pagination from repository
            $service->setPages($repository->getPages());

            if ($repository->isCacheble()) {
                $repository
                    ->setHashName($hash)
                    ->setToCache($ids);

                // кешируем объект для пагинации
                $repository
                    ->setHashName($service->pagination_prefix . $repository->getHashName())
                    ->setToCache($repository->getPages());
            }
        }

        if ($ids) {
            $items = $service->getByIds($ids);

            $pages = $repository
                ->setHashName($service->pagination_prefix . $repository->getHashName())
                ->getFromCache(false, $service->cache_params);

            if ($pages)
                $service->setPages($pages);
        }

        $service->setItems($items);

        return $service->getItems();
    }
}

