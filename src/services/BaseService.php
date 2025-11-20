<?php

namespace pribolshoy\repository\services;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\filters\ServiceFilter;
use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\FilterInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\structures\HashtableCollisionStructure;
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

    protected array $adding_structures = [];

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

    protected ?FilterInterface $filter = null;

    protected string $filter_class = ServiceFilter::class;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize service.
     * Can be overridden in child classes.
     *
     * @return void
     */
    protected function init()
    {
    }

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
    public function setPrimaryKeys(array $primaryKeys): BaseServiceInterface
    {
        $this->primaryKeys = $primaryKeys;
        return $this;
    }

    /**
     * Get Items structure object.
     * TODO: make protected
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
            } elseif (!class_exists($class)) {
                throw new ServiceException('Item structure class not found: ' . $this->item_structure_class ?? 'empty');
            }

            $this->item_structure = new $class($this);
        }

        return $this->item_structure;
    }

    /**
     * Get Items hashtable structure object.
     * TODO: make protected
     *
     * @param bool $refresh
     *
     * @return StructureInterface|HashtableStructure
     * @throws ServiceException
     */
    public function getBasicHashtableStructure(bool $refresh = false):HashtableStructure
    {
        if (is_null($this->hashtable_item_structure) || $refresh) {
            $class = $this->hashtable_item_structure_class;

            if (!$class) {
                throw new ServiceException('Property hashtable_item_structure_class is not set');
            } elseif (!class_exists($class)) {
                throw new ServiceException('Item structure class not found: ' . $this->hashtable_item_structure_class ?? 'empty');
            }

            $this->hashtable_item_structure = new $class($this);
        }

        return $this->hashtable_item_structure;
    }

    /**
     * @return array
     */
    public function getNamedStructures(): array
    {
        return $this->adding_structures;
    }

    /**
     * @param string $name
     *
     * @return StructureInterface|null
     * @throws ServiceException
     */
    public function getNamedStructure(string $name): ?StructureInterface
    {
        $structure = $this->adding_structures[$name] ?? null;

        if (is_array($structure)) {
            $structure = $this->initAddingStructure($structure);
            $this->insertAddingStructure($name, $structure);
        }

        return $structure;
    }

    /**
     * @param string $name
     * @param StructureInterface $structure
     *
     * @return object
     */
    protected function insertAddingStructure(
        string $name,
        StructureInterface $structure
    ): object {
        $this->adding_structures[$name] = $structure;
        return $this;
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
        } elseif (class_exists($this->repository_class)) {
            return $this->repository_class;
        } elseif (class_exists($this->repository_path . $this->repository_class)) {
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
    public function getRepository(array $params = []): RepositoryInterface
    {
        $class = $this->getRepositoryClass();
        /** @var $repository RepositoryInterface */
        $repository =  new $class($params);

        if (!$repository instanceof RepositoryInterface) {
            throw new ServiceException("Repository must implement RepositoryInterface");
        }

        return $repository;
    }

    /**
     * @param string $repository_class
     *
     * @return object
     */
    public function setRepositoryClass(string $repository_class): BaseServiceInterface
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
     * @throws ServiceException
     */
    public function setItems(array $items): void
    {
        $this->getItemStructure()->setItems($items);
        $this->updateHashtable();
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
    public function addItem($item, bool $replace_if_exists = true): BaseServiceInterface
    {
        $update = false;

        if ($this->getItems()) {
            $item_key = $this->getBasicHashtableStructure()
                ->getByKey($item);

            // if exists and we want to replace
            if (!is_null($item_key) && $replace_if_exists) {
                $this->getItemStructure()
                    ->addItem($item, $item_key);

                $update = true;
            } elseif (is_null($item_key)) {
                // item don't exists in items yet
                $this->getItemStructure()
                    ->addItem($item);
                $update = true;
            }
        } else {
            $this->getItemStructure()
                ->addItem($item);
            $update = true;
        }

        if ($update) {
            // update after adding
            $this->updateHashtable();
        }

        return $this;
    }

    /**
     * Get hash by item using its primary key.
     *
     * @param $item
     *
     * @return string
     */
    public function getItemHash($item)
    {
        $key = $this->getItemPrimaryKey($item) ?: serialize($item);
        return $this->hash($key);
    }

    /**
     * TODO: move realization in object Hasher
     * @param $value
     *
     * @return string
     */
    public function hash($value) :string
    {
        return md5($value);
    }

    /**
     * TODO: make protected
     * Must be realized in child because
     * $item can be specific type object
     * and have special method for taking primary key.
     *
     * @param $item
     *
     * @return mixed
     */
    abstract public function getItemPrimaryKey($item);


    /**
     * Wrapper upon hashtable_item_structure object
     *
     * @return array
     * @throws exceptions\ServiceException
     */
    public function getHashtable()
    {
        return $this->getBasicHashtableStructure()
            ->getItems();
    }

    /**
     * Update hashtable. Make new from actual items.
     *
     * TODO: make protected???
     *
     * @return $this
     * @throws exceptions\ServiceException
     */
    public function updateHashtable() :BaseServiceInterface
    {
        $this->getBasicHashtableStructure()
            ->setItems($this->getItems() ?? []);

        if ($this->getNamedStructures()) {
            foreach ($this->getNamedStructures() as $name => $structure) {
                $structure = $this->getNamedStructure($name);
                $structure->setItems($this->getItems() ?? []);
            }
        }

        return $this;
    }

    /**
     * @param array $config
     *
     * @return StructureInterface
     * @throws ServiceException
     */
    protected function initAddingStructure(array $config):StructureInterface
    {
        $class = $config['class'] ?? null;
        unset($config['class']);

        if (!$class || !class_exists($class)) {
            throw new ServiceException('Adding item structure class not found: ' . $class ?? 'empty');
        }

        /** @var StructureInterface $structure */
        $structure = new $class($this);
        $structure->addParams($config);

        return $structure;
    }

    /**
     * @param string $filter_class
     *
     * @return object
     */
    public function setFilterClass(string $filter_class): BaseServiceInterface
    {
        $this->filter_class = $filter_class;
        return $this;
    }

    /**
     * @param bool $refresh
     *
     * @return FilterInterface
     */
    public function getFilter(bool $refresh = false): FilterInterface
    {
        if (!$this->filter || $refresh) {
            $class = $this->filter_class;
            $this->filter = new $class($this);
        }

        return $this->filter;
    }

    /**
     * @param array $sorting
     *
     * @return $this
     */
    public function setSorting(array $sorting): BaseServiceInterface
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
    public function getByExp(array $attributes): array
    {
        return $this->getFilter()->getByExp($attributes) ?? [];
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes): array
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
    public function getByIds(array $ids, array $attributes = []): array
    {
        return $this->getFilter()->getByIds($ids, $attributes) ?? [];
    }
}
