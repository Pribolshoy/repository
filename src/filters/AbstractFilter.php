<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\UsedByServiceInterface;
use pribolshoy\repository\traits\UsedByServiceTrait;

/**
 * Class AbstractFilter
 *
 */
abstract class AbstractFilter implements UsedByServiceInterface
{
    use UsedByServiceTrait;

    public function __construct(AbstractService $service)
    {
        $this->service = $service;
    }

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
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
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
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     *
     *
     * @param $item
     * @param array $attributes
     *
     * @return array
     */
    public function filterByAttributes($item, array $attributes)
    {
        $result = [];

        if ($attributes) {
            foreach ($attributes as $name => $value) {
                if (is_string($value)) $value = [$value];

                $itemAttrValue = $this->getService()
                    ->getItemAttribute($item, $name);

                if (!$itemAttrValue
                    || !in_array($itemAttrValue, $value)
                ) {
                    continue;
                }

                $result = $item;
            }
        } else {
            $result = $item;
        }

        return $result;
    }
}

