<?php

namespace pribolshoy\repository;

use pribolshoy\repository\filters\PaginatedServiceFilter;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;

/**
 * Class PaginatedCachedService
 *
 * Use for caching of entities IDs of filtered
 * pagination list.
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
     * @param RepositoryInterface|null $repository
     * @param bool $refresh_repository_cache
     *
     * @return mixed
     * @throws \Exception
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false)
    {
        return $this;
    }

    /**
     * Delete all pagination cache from storage by hash.
     *
     * @param CachebleRepositoryInterface|null $repository
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = [])
    {
        /** @var $repository AbstractCachebleRepository */
        if (!$repository)
            $repository = $this->getRepository($params);

        // entities
        $repository
            ->setHashName($this->getHashPrefix() . $repository->getHashPrefix())
            ->deleteFromCache();

        // pagination
        $repository
            ->setHashName(
                $this->pagination_prefix
                . $this->getHashPrefix()
                . $repository->getHashPrefix()
            )
            ->deleteFromCache();

        return true;
    }
}
