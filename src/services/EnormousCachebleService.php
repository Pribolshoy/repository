<?php

namespace pribolshoy\repository\services;

use pribolshoy\repository\services\AbstractCachebleService;
use pribolshoy\repository\filters\EnormousServiceFilter;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\EnormousServiceInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\Logger;

/**
 * Class EnormousCachebleService
 *
 * Implements logic of processing entities with enormous rows count,
 * usually more than 1000.
 *
 * It put in storage all rows, with desired filtering by repository.
 * Also, this service put in cache alias hash table.
 *
 * In this service we don't have getList method, we just take rows
 * by its ID or Alias.
 */
abstract class EnormousCachebleService extends AbstractCachebleService implements EnormousServiceInterface
{
    protected string $filter_class = EnormousServiceFilter::class;

    protected int $max_init_iteration = 10;

    /**
     * How much times was ran method
     * initStorage() recursively
     *
     * @var null|int
     */
    protected ?int $init_iteration = null;

    protected bool $is_fetching = false;

    protected string $hash_prefix = 'detail:';

    protected bool $use_alias_cache = true;

    public array $cache_params = [
        'get' => [
            'strategy' => 'hash'
        ],
        'set' => [
            'strategy' => 'hash'
        ]
    ];

    /**
     * Getter of max_init_iteration
     *
     * @return int
     */
    public function getMaxInitIteration(): int
    {
        return $this->max_init_iteration;
    }

    /**
     * Setter of init_iteration
     *
     * @param null|int $init_iteration
     *
     * @return $this
     */
    public function setInitIteration(?int $init_iteration = null): EnormousServiceInterface
    {
        $this->init_iteration = $init_iteration;
        return $this;
    }

    /**
     * Getter of init_iteration
     *
     * @return int|null
     */
    public function getInitIteration(): ?int
    {
        return $this->init_iteration;
    }

    /**
     * Setter of is_fetching
     *
     * @param  bool $is_fetching
     *
     * @return EnormousCachebleService
     */
    public function setIsFetching(bool $is_fetching): EnormousServiceInterface
    {
        $this->is_fetching = $is_fetching;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isFetching()
    {
        return $this->is_fetching ?? false;
    }

    /**
     * @override
     *
     * @param mixed $key string with key, or array|object with keys
     *                             which are used for hashtable keys.
     * @param string|null $structureName
     *
     * @return mixed|array
     * @throws exceptions\ServiceException
     * @throws \Exception
     */
    public function getByHashtable(
        $key,
        ?string $structureName = null
    ) {
        if (!$this->getItems()) {
            return [];
        }

        return parent::getByHashtable($key, $structureName);
    }

    /**
     * @override
     * @deprecated
     *
     * Method is deprecated because of it can give wrong results.
     * For example, if memory hashtable store not all items,
     * then it returns only some of them.
     * At the time, all need items can be obtained by other methods.
     *
     * @param $keys
     * @param string|null $structureName
     *
     * @return array
     * @throws exceptions\ServiceException
     * @throws \Exception
     */
    public function getByHashtableMulti(
        $keys,
        ?string $structureName = null
    ) {
        throw new \Exception('Method ' . __METHOD__ . ' is deprecated!');
    }

    /**
     *
     * @param CachebleRepositoryInterface|RepositoryInterface|null $repository
     * @param bool $refresh_repository_cache ignored
     *
     * @return $this
     * @throws \Exception
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false): CachebleServiceInterface
    {
        $this->setItems([]);

        // null mean that it first run of this method
        if (is_null($this->getInitIteration())) {
            // at the first run - clear repository anyway
            $this->clearStorage($repository);
            $this->setInitIteration(1);
            $fetched_items = 0;
        } else {
            $this->setInitIteration($this->getInitIteration() + 1);
            $fetched_items = ($this->getInitIteration() - 1) * $this->getFetchingStep();
        }

        /** @var $repository RepositoryInterface */
        if (!$repository) {
            $repository = $this->getRepository(
                [
                    'limit'     => $this->getFetchingStep(),
                    'offset'    => ($this->getInitIteration() - 1) * $this->getFetchingStep(),
                ]
            );
        }

        $this->setIsFromCache(false);

        // if rows were found - set it to items
        if ($items = $repository->search()) {
            // preparing
            foreach ($items as &$mutableItem) {
                $mutableItem = $this->prepareItem($mutableItem);
            }

            $this->setItems($items);

            $this->setIsFetching(($repository->getTotalCount() > ($fetched_items + count($items))));

            // if repos is cacheble - set items to cache
            if ($repository->isCacheble()) {
                $baseHashName = $this->getHashPrefix() . $repository->getHashPrefix();
                foreach ($items as $item) {
                    $hash_name = $baseHashName
                        . $this->getIdPostfix() . $this->getItemIdValue($item);

                    $repository->setHashName($hash_name)
                        ->setToCache($item, $this->getCacheParams('set'));
                }
                
                Logger::log('initStorage', $baseHashName, 'service', $items);
            }
        }

        $this->afterInitStorage($repository);

        return $this;
    }

    /**
     * Event for storage initiation.
     * Better to prefer run storage initiation
     * by it, and not by initStorage().
     * Because storage initiation can contain
     * some important complex logic before initStorage().
     *
     * Synchronous.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function initStorageEvent(): bool
    {
        if ($this->wasInitStorageFired()) {
            return false;
        }

        $this->initStorageFired();
        $this->setIsFetching(true);

        // doing initiation in cycle
        for ($i = 0; $i <= $this->getMaxInitIteration() && $this->isFetching(); $i++) {
            $this->initStorage();
        }

        $this->setInitIteration(null);

        return true;
    }
}
