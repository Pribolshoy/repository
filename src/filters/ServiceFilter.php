<?php

namespace pribolshoy\repository\filters;

/**
 * Class AbstractEntityService
 *
 * @package app\repositories
 */
class ServiceFilter extends AbstractFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        return $this->getService()->getItems();
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
                    if (!$this->getService()->hasItemAttribute($item, $name)) continue 2;
                    if ($value === false || is_null($value)) continue;
                    if (preg_match("#$value#iu", $item->$name) == false) {
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
                    if (!$this->getService()->hasItemAttribute($item, $name)) continue 2;
                    if ($value === false || is_null($value)) continue;
                    if ($item->$name !== $value) continue 2;
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
                    if (!$this->getService()->hasItemAttribute($item, $name)) continue 2;
                    if ($value === false || is_null($value)) continue;
                    if ($item->$name !== $value) continue 2;
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
        //$this->>getService()->getFilter()->getById($id, $attributes);
        if ($items = $this->getService()->getList()) {
            foreach ($items as $item) {
                if ($this->getService()->getItemPrimaryKey($item) == $id) {
                    if ($attributes) {
                        foreach ($attributes as $name => $value) {
                            if (!$this->getService()->hasItemAttribute($item, $name)) continue 2;
                            if ($value === false || is_null($value)) continue;
                            if ($item->$name != $value) continue 2;
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

