<?php

namespace pribolshoy\repository\filters;

use Exception;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Class ServiceFilter
 *
 */
class ServiceFilter extends AbstractFilter
{
    /**
     * @param array $params
     * @param bool $cache_to not use
     *
     * @return array|null
     */
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        /** @var $service ServiceInterface|CachebleServiceInterface */
        $service = $this->getService();
        $service->initStorage();
        if (is_null($service->getItems())) {
            /** @var $repository RepositoryInterface */
            $repository = $service->getRepository($params);

            if ($items = $repository->search()) {
                $service->setItems($service->sort($items));
            }
        }

        return $service->getItems() ?? [];
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws Exception
     */
    public function getByExp(array $attributes): array
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        $result = [];
        if ($items = $service->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) {
                        continue;
                    }
                    if (is_null($itemAttrValue = $service->getItemAttribute($item, $name))) {
                        continue 2;
                    }
                    if (preg_match("#$value#iu", $itemAttrValue) == false) {
                        continue 2;
                    }
                }

                $result[] = $item;
            }
            // resort results
            $result = $this->getService()->sort($result);
        }

        return $result;
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws Exception
     */
    public function getByMulti(array $attributes): array
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        $result = [];
        if ($items = $service->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) {
                        continue;
                    }
                    if ($service->getItemAttribute($item, $name) !== $value) {
                        continue 2;
                    }
                }

                $result[] = $item;
            }

            // resort results
            $result = $this->getService()->sort($result);
        }

        return $result;
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getBy(array $attributes)
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        if ($items = $service->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) {
                        continue;
                    }
                    if ($service->getItemAttribute($item, $name) !== $value) {
                        continue 2;
                    }
                }

                return $item;
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @param array $attributes
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getById(int $id, array $attributes = [])
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        if ($items = $service->getList()) {
            foreach ($items as $item) {
                if ($service->getItemPrimaryKey($item) == $id) {
                    if ($attributes) {
                        foreach ($attributes as $name => $value) {
                            if ($value === false || is_null($value)) {
                                continue;
                            }
                            if ($service->getItemAttribute($item, $name) != $value) {
                                continue 2;
                            }
                        }
                    }

                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws Exception
     */
    public function getByIds(array $ids, array $attributes = []): array
    {
        $result = [];

        foreach ($ids as $id) {
            if ($item = $this->getById($id, $attributes)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
