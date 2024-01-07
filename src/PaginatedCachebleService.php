<?php

namespace pribolshoy\repository;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractCachebleService;
use pribolshoy\repository\filters\PaginatedServiceFilter;

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
    protected string $hash_prefix = 'list_';

    public string $pagination_prefix = 'pagination_';

    public array $cache_params = [
        'strategy' => 'getValue'
    ];

    protected string $filter_class = PaginatedServiceFilter::class;

    /**
     * For paginated services initiation items to cache storage
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
     * Delete all pagination cache from storage by hash.
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
            ->setHashName($this->getHashPrefix() . $repository->getHashPrefix())
            ->deleteFromCache();

        // пагинации элементов
        $repository
            ->setHashName(
                $this->pagination_prefix
                . $this->getHashPrefix()
                . $repository->getHashPrefix()
            )
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
//    public function refreshItem(array $params)
//    {
//        /** @var $repository AbstractCachebleRepository */
//        $repository = $this->getRepository($params);
//
//        if (($items = $repository->search())
//            && $repository->isCacheble()
//        ) {
//            $repository->setToCache($items);
//
//            // кешируем объект для пагинации
//            $repository->setHashName($repository->getTotalHashName())
//                ->setToCache($repository->getPages());
//        } else {
//            $this->clearStorage();
//        }
//
//        return true;
//    }

    /**
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [])
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }
}
