<?php

namespace pribolshoy\repository\filters;

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
        /** @var $service ServiceInterface */
        $service = $this->getService();

        if (!is_null($service->getItems())) {
            $items = $service->getItems();
        } else {
            /** @var $repository RepositoryInterface */
            $repository = $service->getRepository($params);
            $items = $repository->search();
        }

        if (!is_null($items)) {
            $service->setItems($service->sort($items));
            $service->updateHashtable();
        }

        return $service->getItems() ?? [];
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes)
    {
        $result = [];
        if ($items = $this->getService()->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) continue;
                    if (is_null($itemAttrValue = $this->getService()->getItemAttribute($item, $name))) continue 2;
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
     * @throws \Exception
     */
    public function getByMulti(array $attributes)
    {
        $result = [];
        if ($items = $this->getService()->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) continue;
                    if ($this->getService()->getItemAttribute($item, $name) !== $value) continue 2;
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
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        if ($items = $this->getService()->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) continue;
                    if ($this->getService()->getItemAttribute($item, $name) !== $value) continue 2;
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
     * @throws \Exception
     */
    public function getById(int $id, array $attributes = [])
    {
        if ($items = $this->getService()->getList()) {
            foreach ($items as $item) {
                if ($this->getService()->getItemPrimaryKey($item) == $id) {
                    if ($attributes) {
                        foreach ($attributes as $name => $value) {
                            if ($value === false || is_null($value)) continue;
                            if ($this->getService()->getItemAttribute($item, $name) != $value) continue 2;
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
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = [])
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

