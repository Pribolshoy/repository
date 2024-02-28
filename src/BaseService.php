<?php

namespace pribolshoy\repository;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\AbstractFilter;
use pribolshoy\repository\filters\ServiceFilter;
use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\structures\HashtableStructure;

/**
 * Class BaseService
 *
 * Abstract class by which extends AbstractService.
 * It contains of straightforward methods without logic
 * which usually will not need in modifying in children.
 * It make AbstractService more thin.
 *
 */
abstract class BaseService implements BaseServiceInterface
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
    protected array $primaryKeys = [];

    /**
     * Sorting in items.
     * @var array
     */
    protected array $sorting = [];

    protected ?StructureInterface $item_structure = null;

    protected string $item_structure_class = ArrayStructure::class;

    protected ?HashtableStructure $hashtable_item_structure = null;

    protected string $hashtable_item_structure_class = HashtableStructure::class;

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
     * Get Items structure object.
     *
     * @param bool $refresh
     *
     * @return StructureInterface
     * @throws ServiceException
     */
    public function getItemStructure(bool $refresh = false):StructureInterface
    {
        if (is_null($this->item_structure) || $refresh) {
            $class = $this->item_structure_class;

            if (!$class) {
                throw new ServiceException('Property item_structure_class is not set');
            } else if (!class_exists($class)) {
                throw new ServiceException('Item structure class not found: ' . $this->item_structure_class ?? 'empty');
            }

            $this->item_structure = new $class($this);
        }

        return $this->item_structure;
    }

    /**
     * Get Items hashtable structure object.
     *
     * @param bool $refresh
     *
     * @return StructureInterface|HashtableStructure
     * @throws ServiceException
     */
    public function getItemHashtableStructure(bool $refresh = false):HashtableStructure
    {
        if (is_null($this->hashtable_item_structure) || $refresh) {
            $class = $this->hashtable_item_structure_class;

            if (!$class) {
                throw new ServiceException('Property hashtable_item_structure_class is not set');
            } else if (!class_exists($class)) {
                throw new ServiceException('Item structure class not found: ' . $this->hashtable_item_structure_class ?? 'empty');
            }

            $this->hashtable_item_structure = new $class($this);
        }

        return $this->hashtable_item_structure;
    }

    /**
     * Get repository class.
     *
     * @return string
     *
     * @throws ServiceException
     */
    protected function getRepositoryClass()
    {
        if (!$this->repository_class) {
            throw new ServiceException('Property repository_class is not set');
        } else if (class_exists($this->repository_class)) {
            return $this->repository_class;
        } else if (class_exists($this->repository_path . $this->repository_class)) {
            return $this->repository_path . $this->repository_class;
        } else {
            throw new ServiceException('Repository class not found: ' . $this->repository_class ?? 'empty');
        }
    }

    /**
     * Get repository object.
     *
     * @param array $params
     *
     * @return RepositoryInterface|CachebleRepositoryInterface
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
     * @return array
     * @throws exceptions\ServiceException
     */
    public function getItems():?array
    {
        return $this->getItemStructure()->getItems();
    }

    /**
     * @param array $items
     *
     * @return BaseService|AbstractService
     * @throws exceptions\ServiceException
     */
    public function setItems(array $items)
    {
        $this->getItemStructure()->setItems($items);
        $this->updateHashtable();
        return $this;
    }

    /**
     * Append item to items.
     *
     * @param $item
     * @param bool $replace_if_exists
     *
     * @return $this
     * @throws exceptions\ServiceException
     */
    public function addItem($item, bool $replace_if_exists = true): object
    {
        if ($this->getItemStructure()->getItems()) {
            $item_key = $this->getItemHashtableStructure()
                ->getByKey($this->getItemHash($item));

            // if exists and we want to replace
            if ($item_key && $replace_if_exists) {
                $this->getItemStructure()
                    ->addItem($item, $item_key);
            } else if (!$item_key) {
                // item don't exists in items yet
                $this->getItemStructure()
                    ->addItem($item);
            }
        } else {
            $this->getItemStructure()
                ->addItem($item);
        }

        // always update after adding
        $this->updateHashtable();

        return $this;
    }

    /**
     * TODO: move realization in object Hasher
     * Get hash by item using its primary key.
     *
     * @param $item
     * @return string
     */
    public function getItemHash($item)
    {
        $key = $this->getItemPrimaryKey($item) ?: serialize($item);
        return md5($key);
    }

    abstract public function getItemPrimaryKey($item);


    /**
     * Wrapper upon hashtable_item_structure object
     *
     * @return array
     * @throws exceptions\ServiceException
     */
    public function getHashtable()
    {
        return $this->getItemHashtableStructure()
            ->getItems();
    }

    /**
     * Update hashtable. Make new from actual items.
     *
     * @return $this
     * @throws exceptions\ServiceException
     */
    public function updateHashtable() :object
    {
        $this->getItemHashtableStructure()
            ->setItems($this->getItems());

        return $this;
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
     * @param array $sorting
     * @return $this
     */
    public function setSorting(array $sorting): object
    {
        $this->sorting = $sorting;
        return $this;
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

