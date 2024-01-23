<?php

namespace pribolshoy\repository;

use pribolshoy\repository\filters\EnormousServiceFilter;
use pribolshoy\repository\interfaces\EnormousServiceInterface;

/**
 * Class EnormousCachebleService
 *
 * Implements logic of processing entities with enormous rows count,
 * usually more than 1000.
 *
 * It put in storage all rows, with desired filtering by repository.
 * Also this service put in cache alias hash table.
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
     * @var int
     */
    protected ?int $init_iteration = null;

    protected bool $is_fetching = false;

    protected string $hash_prefix = 'detail_';

    protected bool $use_alias_cache = true;

    public array $cache_params = [
        'strategy' => 'getValue'
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
     * @param  int $init_iteration
     *
     * @return $this
     */
    public function setInitIteration(?int $init_iteration): object
    {
        $this->init_iteration = $init_iteration;
        return $this;
    }

    /**
     * Getter of init_iteration
     *
     * @return int
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
    public function setIsFetching(bool $is_fetching): object
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
     *
     * @param null $repository
     * @param bool $refresh_repository_cache ignored
     *
     * @return $this|AbstractCachebleService
     * @throws \Exception
     */
    public function initStorage($repository = null, $refresh_repository_cache = false)
    {
        $this->setItems([]);

        // null mean that it first run of this method
        if (is_null($this->getInitIteration())) {
            // at the first run - clear repository anyway
            $this->clearStorage($repository);
            $this->setInitIteration(1);
            $fetched_items = 0;
        } else {
            $this->setInitIteration($this->getInitIteration()+1);
            $fetched_items = ($this->getInitIteration()-1) * $this->getFetchingStep();
        }

        /** @var $repository AbstractCachebleRepository */
        if (!$repository) {
            $repository = $this->getRepository(
                [
                    'limit'     => $this->getFetchingStep(),
                    'offset'    => ($this->getInitIteration()-1) * $this->getFetchingStep(),
                ]
            );
        }

        $this->setIsFromCache(false);

        // if rows were found - set it to items
        if ($items = $repository->search()) {
            // preparing
            foreach ($items as &$item) {
                $item = $this->prepareItem($item);
            }

            $this->setItems($items);

            $this->setIsFetching(($repository->getTotalCount() > ($fetched_items + count($items))));

            // if repos is cacheble - set items to cache
            if ($repository->isCacheble()) {
                foreach ($items as $item) {
                    $hash_name = $this->getHashPrefix()
                        . $repository->getHashPrefix()
                        . ':' . $this->getItemPrimaryKey($item);

                    $repository
                        ->setHashName($hash_name)
                        ->setToCache($item);
                }
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
        for (
            $i = 0;
            $i <= $this->getMaxInitIteration() && $this->isFetching();
            $i++
        ) {
            $this->initStorage();
        }

        $this->setInitIteration(null);

        return true;
    }

}
