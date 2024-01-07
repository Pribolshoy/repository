<?php

namespace pribolshoy\repository;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\AbstractFilter;
use pribolshoy\repository\filters\ServiceFilter;
use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Class AbstractEntityService
 *
 * Abstract class for realization of service object
 * by which we can using Repositoriess.
 *
 * @package app\repositories
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * Elements queried by repository
     * @var array
     */
    protected array $items = [];

    /**
     * @var array
     */
    protected array $hashtable = [];


    /**
     * @var array Сортировка элементов. Может переопределяться
     */
    protected array $sorting = ['name' => SORT_ASC];

    /**
     * @var string namespace где хранятся репозитории.
     */
    protected ?string $repository_path = "";

    /**
     *
     * @var string namespace класса репозитория.
     * Нужен для создания репозитрия через который
     * сервис будет получать и кешировать элементы.
     */
    protected ?string $repository_class = "";

    protected ?AbstractFilter $filter = null;

    protected string $filter_class = ServiceFilter::class;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init() {}

    /**
     * Get repository class.
     *
     * @return string|null
     *
     * @throws ServiceException
     */
    protected function getRepositoryClass()
    {
        if (!$this->repository_class) {
             throw new ServiceException('Не задан атрибут repository_class');
        } else if (class_exists($this->repository_class)) {
            return $this->repository_class;
        } else if (class_exists($this->repository_path . $this->repository_class)) {
            return $this->repository_path . $this->repository_class;
        } else {
            throw new ServiceException('Repository class not found: ' . $this->repository_class ?? 'empty');
        }
    }

    /**
     * @param string $repository_class
     * @return object
     */
    public function setRepositoryClass(string $repository_class): object
    {
        $this->repository_class = $repository_class;
        return $this;
    }

    /**
     * Get repository object.
     *
     * @param array $params
     *
     * @return object
     * @throws ServiceException
     */
    public function getRepository(array $params = []): object
    {
        $class = $this->getRepositoryClass();
        /** @var $repository AbstractRepository */
        $repository =  new $class($params);

        if (!$repository instanceof AbstractRepository)
            throw new ServiceException("Репозиторий должен наследовать класс AbstractRepository");

        return $repository;
    }

    /**
     * @param string $filter_class
     *
     * @return object
     */
    public function setFilterClass(string $filter_class): object
    {
        $this->filter_class = $filter_class;
        return $this;
    }

    /**
     * @param bool $refresh
     *
     * @return AbstractFilter
     */
    public function getFilter(bool $refresh = false): AbstractFilter
    {
        if (!$this->filter || $refresh) {
            $class = $this->filter_class;
            $this->filter = new $class($this);
        }

        return $this->filter;
    }

    public function getList(array $params = [], bool $cache_to = true) : ?array
    {
        return $this->getFilter()->getList($params, $cache_to);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     *
     * @return AbstractService
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Append item to items.
     *
     * @param $item
     * @param bool $replace_if_exists
     *
     * @return bool
     */
    public function addItem($item, bool $replace_if_exists = true)
    {
        if ($this->getItems()) {
            if ($item_key = $this->getHashValue($this->getHashByItem($item))
                && $replace_if_exists) {
                $this->items[$item_key] = $item;
            } else if (!$item_key) {
                $this->items[] = $item;
            }
        } else {
            $this->setItems([$item]);
        }

        return true;
    }

    /**
     * Need child realization
     *
     * @return mixed
     */
    abstract public function getItemPrimaryKey($item);

    abstract public function hasItemAttribute($item, string $name) :bool;

    abstract public function getItemAttribute($item, string $name);

    /**
     * Return array if items primary keys.
     *
     * @param array $items
     *
     * @return array
     */
    public function collectItemsPrimaryKeys(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->getItemPrimaryKey($item);
        }

        return $result;
    }

    /**
     * Collecting items value
     *
     * @param array $items
     * @param string $name name of item attribute to collect
     *
     * @return array
     */
    protected function collectItemsValue(array $items, string $name): array
    {
        $result = [];
        foreach ($items as $item) {
            if ($value = $item[$name]) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Get hash by item using its primary key.
     *
     * @param $item
     * @return string
     */
    public function getHashByItem($item)
    {
        return md5($this->getItemPrimaryKey($item));
    }

    /**
     * Update hashtable. Make new from actual items.
     *
     * @return bool
     */
    public function updateHashtable()
    {
        if ($items = $this->getItems()) {
            $this->hashtable = [];
            foreach ($items as $key => $item) {
                $this->hashtable[$this->getHashByItem($item)] = $key;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getHashtable()
    {
        return $this->hashtable;
    }

    /**
     * Get value from hashtable by hash.
     * Value is position if concrete item in items.
     *
     * @param string $hash
     * @return mixed|null
     */
    public function getHashValue(string $hash)
    {
        $hashtable = $this->getHashtable();

        if ($hashtable
            && array_key_exists($hash, $hashtable)
        ) {
            return $hashtable[$hash];
        }
        return null;
    }

    /**
     * Get item by hash value
     *
     * @param string $hash
     * @return mixed|null
     */
    public function getItemByHash(string $hash)
    {
        if ($key = $this->getHashValue($hash)
            && $this->getItems()
        ) {
            return $this->getItems()[$key] ?? null;
        }
        return null;
    }

    /**
     * @param array $sorting
     * @return $this
     */
    public function setSorting(array $sorting): object
    {
        $this->sorting = $sorting;
        return $this;
    }

    abstract public function sort(array $items);



    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes)
    {
        return $this->getFilter()->getByExp($attributes) ?? [];
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes)
    {
        return $this->getFilter()->getByMulti($attributes) ?? [];
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        return $this->getFilter()->getBy($attributes) ?? [];
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
        return $this->getFilter()->getById($id, $attributes) ?? [];
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
        return $this->getFilter()->getByIds($ids, $attributes) ?? [];
    }
}

