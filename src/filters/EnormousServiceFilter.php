<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\AbstractService;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\EnormousServiceInterface;

/**
 * Class EnormousServiceFilter
 *
 */
class EnormousServiceFilter extends CachebleServiceFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

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
                ->setHashName(
                    $this->getService()->getHashPrefix()
                    . $repository->getHashPrefix()
                )
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
                ->setParams(
                    ['ids' => $ids],
                    true,
                    true
                )
                ->search();
        }

        return $items ?? [];
    }

    /**
     * @override
     *
     * @param string $alias
     * @param array $attributes
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getByAlias(string $alias, array $attributes = [])
    {
        /** @var CachebleServiceInterface $service */
        $service = $this->getService();

        if ($item = parent::getByAlias($alias, $attributes)) {
            if ($item) {
                $service->addItem($item, false);
            }
        }

        return $item ?? [];
    }
}

