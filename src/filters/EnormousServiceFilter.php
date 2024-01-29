<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractService;

/**
 * Class EnormousServiceFilter
 *
 */
class EnormousServiceFilter extends AbstractFilter
{
    /**
     * @param int $id
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getById(int $id, array $attributes = [])
    {
        $item = $this->getByIds([$id], $attributes);

        return $item[0] ?? [];
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
        /** @var AbstractCachebleRepository $repository */
        $repository = $this->getService()->getRepository();
        $params = array_merge($this->getService()->cache_params, ['fields' => $ids]);

        $fetch_from_repository = false;

        if ($this->getService()->isUseCache()) {
            $items = $repository
                ->setHashName($this->getService()->getHashPrefix() . $repository->getHashPrefix())
                ->getFromCache(false, $params);

            // if cache not exists - do init storage for all items
            if (!$items) {
                if ($repository->isCacheble()
                    && !$this->getService()->isCacheExists($repository)
                ) {
                    $this->getService()->initStorageEvent();
                    $fetch_from_repository = true;
                } else if (!$repository->isCacheble()) {
                    $fetch_from_repository = true;
                }
            }
        } else {
            $fetch_from_repository = true;
        }

        if ($fetch_from_repository) {
            $items = $repository
                ->setParams(['ids' => $ids], true, true)
                ->search();
        }

        return $items ?? [];
    }

    /**
     * Get item by alias throw alias hash table.
     *
     * @param string $alias
     * @param array $attributes
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getByAlias(string $alias, array $attributes = [])
    {
        /** @var AbstractCachebleRepository $repository */
        $primaryKey = $this->getPrimaryKeyByAlias(
            $alias,
            $repository = $this->getService()->getRepository()
        );

        if ($primaryKey) {
            $fetch_from_repository = false;

            if ($this->getService()->isUseCache()) {
                $item = $repository
                    ->setHashName(
                        $this->getService()->getHashPrefix()
                        . $repository->getHashPrefix()
                        . ':' . $primaryKey
                    )
                    ->getFromCache();

                if (!$item) {
                    if ($repository->isCacheble()
                        && !$this->getService()->isCacheExists()
                    ) {
                        $this->getService()->initStorageEvent();
                        $fetch_from_repository = true;
                    } else if (!$repository->isCacheble()) {
                        $fetch_from_repository = true;
                    }
                }
            } else {
                $fetch_from_repository = true;
            }

            if ($fetch_from_repository) {
                // get primary key by repository
                // because cache intiation may by sends to queue
                $items = $repository
                    ->setParams(
                        ['id' => $primaryKey], // TODO: replace id by primaryKey
                        true,
                        true
                    )
                    ->search();

                if ($item = $items[0] ?? null) {
                    $item = $this->getService()->prepareItem($item);
                    $item = $this->filterByAttributes($item, $attributes);
                }
            }
        }

        return $item ?? [];
    }

    /**
     * Get primary key from alias hash table.
     *
     * @param string $alias
     * @param AbstractCachebleRepository|null $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPrimaryKeyByAlias(string $alias, ?AbstractCachebleRepository $repository = null)
    {
        /** @var AbstractCachebleRepository $repository */
        if (!$repository) $repository = $this->getService()->getRepository();

        $fetch_from_repository = false;

        if ($this->getService()->isUseCache()) {
            $alias_hash = $this->getService()->getHashPrefix()
                . $repository->getHashPrefix()
                . $this->getService()->getAliasPostfix()
                . ':' . $alias;

            $primaryKey = $repository
                ->setHashName($alias_hash)
                ->getFromCache();

            // if primary key not found - checks if cache exists
            if (!$primaryKey) {
                if ($repository->isCacheble()
                    && !$this->getService()->isCacheExists($repository)
                ) {
                    $this->getService()->initStorageEvent();
                    $fetch_from_repository = true;
                } else if (!$repository->isCacheble()) {
                    $fetch_from_repository = true;
                }
            }

        } else {
            $fetch_from_repository = true;
        }

        if ($fetch_from_repository) {
            // get primary key throw repository
            // because cache intiation may by sends to queue
            $items = $repository
                ->setParams([$this->getService()->getAliasAttribute() => $alias], true, true)
                ->search();

            if ($item = $items[0] ?? null) {
                $primaryKey = $this->getService()->getItemPrimaryKey($item);
            }
        }

        return $primaryKey ?? null;
    }

}

