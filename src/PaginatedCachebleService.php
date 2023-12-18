<?php

namespace pribolshoy\repository;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractCachebleService;

/**
 * Class PaginatedCachedService
 *
 * Обертка над CommonEntityService предоставляющая функционал
 * для работы с сущностями имеющими более 1000 строк в таблице.
 *
 * В кеш отправляется не все содержимое таблицы, а какое то
 * определенныое количество страниц пагинации.
 *
 * @package app\services
 */
abstract class PaginatedCachebleService extends AbstractCachebleService
{
    protected string $list_prefix = 'list_';

    protected string $pagination_prefix = 'pagination_';

    protected array $cache_params = [
        'strategy' => 'getValue'
    ];

    /**
     * For enormous services initiation items to cache storage
     * is not obvious operation and it can't be unified.
     * By default it is stubbed.
     *
     * @param null $repository
     * @param bool $refresh_repository_cache
     *
     * @return mixed
     * @throws \Exception
     */
    public function initStorage($repository = null, $refresh_repository_cache = false)
    {
        return $this;
    }

    /**
     * Delete all cache from storage by hash.
     *
     * @param null $repository
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function clearStorage($repository = null, array $params = [])
    {
        /** @var $repository AbstractCachebleRepository */
        if (!$repository)
            $repository = $this->getRepository($params);

        // сами элементы
        $repository
            ->setHashName($repository->getHashPrefix() . ':*')
            ->deleteFromCache();

        // пагинации элементов
        $repository
            ->setHashName($repository->getTotalHashPrefix() . ':*')
            ->deleteFromCache();

        return true;
    }

    /**
     *
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function refreshItem(array $params)
    {
        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository($params);

        if (($items = $repository->search())
            && $repository->isCacheble()
        ) {
            $repository->setToCache($items);

            // кешируем объект для пагинации
            $repository->setHashName($repository->getTotalHashName())
                ->setToCache($repository->getPages());
        } else {
            $this->clearStorage();
        }

        return true;
    }

    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository($params);

        // устанавливаем разрешение использованя кеширования репозиторию
        $repository->setActiveCache($cache_to);

        $hash = $this->list_prefix . $repository->getHashName();

        // если в сервисе разрешено использования кеширования - пытаемся получить из кеша
        $ids = [];
        if ($this->isUseCache())
            $ids = $repository
                ->setHashName($hash)
                ->getFromCache(false, $this->cache_params);

        // if no data in cache - try to fetch it from repository
        if (!$ids && $items = $repository->search()) {
            $ids = $this->collectItemsPrimaryKeys($items);

            // get pagination from repository
            $this->setPages($repository->getPages());

            if ($repository->isCacheble()) {
                $repository
                    ->setHashName($hash)
                    ->setToCache($ids);

                // кешируем объект для пагинации
                $repository
                    ->setHashName($this->pagination_prefix . $repository->getHashName())
                    ->setToCache($repository->getPages());
            }
        }

        if ($ids) {
            $items = $this->getByIds($ids);

            $pages = $repository
                ->setHashName($this->pagination_prefix . $repository->getHashName())
                ->getFromCache(false, $this->cache_params);

            if ($pages)
                $this->setPages($pages);
        }

        $this->setItems($items);

        return $this->getItems();
    }

    public function getById(int $id, array $attributes = [])
    {
        /** @var $repository AbstractCachebleRepository */
        $repository = $this->getRepository(['id' => $id]);

        return $repository->search()[0] ?? null;
    }

    /**
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [])
    {
        throw new \Exception('Using PaginatedCachebleService::getByIds() must be realized in child!');
    }
}
