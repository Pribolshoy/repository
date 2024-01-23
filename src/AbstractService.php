<?php

namespace pribolshoy\repository;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\AbstractFilter;
use pribolshoy\repository\filters\ServiceFilter;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Class AbstractEntityService
 *
 * Abstract class for realization of service object
 * by which we can using Repositories.
 *
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * Either primary key contains all keys in
     * $primaryKeys property or the first existing.
     * @var bool
     */
    protected bool $multiplePrimaryKey = true;

    /**
     * Names of items properties which we can call
     * primary keys.
     * @var array
     */
    protected array $primaryKeys = [
        'id',
    ];

    /**
     * Elements fetched by repository.
     * @var array
     */
    protected ?array $items = null;

    /**
     *
     * @var array
     */
    protected array $hashtable = [];

    /**
     * Sorting in items.
     * @var array
     */
    protected array $sorting = ['name' => SORT_ASC];

    /**
     * Namespace of dir with repositories classes.
     * @var string
     */
    protected ?string $repository_path = "";

    /**
     * Name or namespace of repository class.
     * @var string
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
     * @return bool
     */
    public function isMultiplePrimaryKey(): bool
    {
        return $this->multiplePrimaryKey;
    }

    /**
     * @param array $primaryKeys
     *
     * @return $this
     */
    public function setPrimaryKeys(array $primaryKeys): object
    {
        $this->primaryKeys = $primaryKeys;
        return $this;
    }

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
     *
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
        /** @var $repository RepositoryInterface */
        $repository =  new $class($params);

        if (!$repository instanceof RepositoryInterface)
            throw new ServiceException("Repository must implement RepositoryInterface");

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

    /**
     * @param array $params
     * @param bool $cache_to
     *
     * @return array|null
     * @throws \Exception
     */
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
            if ($item_key = $this->getHashtableValue($this->getHashByItem($item))
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
     * TODO: move all interactions with items to object Items or so
     * Get primary key from item.
     * It can be multiple.
     *
     * @return mixed
     */
    public function getItemPrimaryKey($item)
    {
        $primaryKey = '';

        foreach ($this->primaryKeys as $name) {
            if ($value = $this->getItemAttribute($item, $name)) {
                $primaryKey .= $value;

                if (!$this->isMultiplePrimaryKey()) {
                    return $primaryKey;
                }
            }
        }

        return $primaryKey;
    }

    /**
     * Check if item has given attribute.
     * Can be realized in child.
     *
     * @param $item
     * @param string $name
     *
     * @return bool
     */
    public function hasItemAttribute($item, string $name): bool
    {
        if (is_array($item)) {
            return array_key_exists($name, $item);
        } elseif (is_object($item)) {
            return isset($item->$name);
        }

        return false;
    }

    /**
     * Get attribute value from item.
     * Can be realized in child.
     *
     * @param $item
     * @param string $name
     *
     * @return mixed
     */
    public function getItemAttribute($item, string $name)
    {
        if (!$this->hasItemAttribute($item, $name)) {
            return null;
        }

        if (is_array($item)) {
            $result = $item[$name];
        } elseif (is_object($item)) {
            $result = $item->$name;
        }

        return $result ?? null;
    }

    /**
     * Collects item's primary keys in array.
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
     * Collects certain item's value in array.
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
            if ($value = $this->getItemAttribute($item, $name)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * TODO: move realization in object Hasher
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
     * @return $this
     */
    public function updateHashtable() :object
    {
        if ($items = $this->getItems()) {
            $this->hashtable = [];
            foreach ($items as $key => $item) {
                $this->hashtable[$this->getHashByItem($item)] = $key;
            }
        }

        return $this;
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
    public function getHashtableValue(string $hash)
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
     * Get item by hash value.
     *
     * @param string $hash hash value for searching
     *                     among hashtable keys.
     * @return mixed|null
     */
    public function getItemByHash(string $hash)
    {
        if (($key = $this->getHashtableValue($hash))
            && $this->getItems()
        ) {
            return $this->getItems()[$key] ?? null;
        }
        return null;
    }

    /**
     * TODO: move realization in object Hashtable
     * Get one item by hashtable.
     *
     * @param $itemWithPrimaryKeys array or object with primary keys
     *                             which are used for hashtable keys.
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getByHashtable($itemWithPrimaryKeys)
    {
        if (is_null($this->getItems())) {
            $this->getList();
        }

        if ($this->getItems()) {
            return $this->getItemByHash($this->getHashByItem($itemWithPrimaryKeys));
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

    /**
     * Process of items sorting.
     * Must be realized in child
     *
     * @param array $items
     *
     * @return mixed
     */
    abstract public function sort(array $items): array;

    public function resort(): object
    {
        if ($items = $this->getItems()) {
            $this->setItems($this->sort($items));
        }
        return $this;
    }

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

