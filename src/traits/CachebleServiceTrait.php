<?php

namespace pribolshoy\repository\traits;

/**
 * Trait CachebleServiceTrait
 *
 * Трейт добавляющий функционал для взаимодействия
 * со списком элементов нуждающимся в пагинации.
 *
 * @package app\components\common\traits
 */
trait CachebleServiceTrait
{

    /**
     *
     * @var bool Можно ли пытаться получить элементы из
     * кеша.
     */
    protected bool $use_cache = true;

    /**
     *
     * @var bool Является ли результат выбранным из  кеша
     */
    protected bool $is_from_cache = true;

    /**
     * @var array Параметры передающиеся в дравер кеша при выборке
     */
    protected array $get_from_cache_params = ['strategy' => 'getAllHash'];

    /**
     * @return bool
     */
    public function isUseCache(): bool
    {
        return $this->use_cache;
    }

    /**
     * @param bool $use_cache
     * @return $this
     */
    public function setUseCache(bool $use_cache)
    {
        $this->use_cache = $use_cache;
        return $this;
    }

    /**
     * @param array $get_from_cache_params
     *
     * @return object
     */
    public function setGetFromCacheParams(array $get_from_cache_params): object
    {
        $this->get_from_cache_params = $get_from_cache_params;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return $this->is_from_cache;
    }

    /**
     * @param bool $is_from_cache
     * @return $this
     */
    public function setIsFromCache(bool $is_from_cache)
    {
        $this->is_from_cache = $is_from_cache;
        return $this;
    }

    /**
     * Обновление одного элемента
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function refreshItem(array $params)
    {
        $class = $this->getRepositoryClass();
        /** @var $repository BaseCachebleRepository */
        $repository = new $class(
            array_merge($params, ['limit' => 1,])
        );

        if ($repository->isCacheble() && $items = $repository->search()) {
            $item = $items[0];
            $hash_name = $repository->getHashName(true, false) . ':' . $item->getPrimaryKey();
            $repository->setHashName($hash_name);
            $repository->setToCache($item);

            // если элементы уже сохранены в объекте
            // то обновляем в нем наш элемент
            if ($this->getItems()) {
                // среди элементов наш уже есть - обновляем
                if ($this->getItemByHash(md5($item->getPrimaryKey()))) {
                    $this->getItems()[$this->getHashValue(md5($item->getPrimaryKey()))] = $item;
                }
            }
        }

        return true;
    }

    /**
     * Иницилизация данных в Хранилище / Кеш.
     * Может переопределяться в наследнике.
     * По умолчанию каждый элемент сохраняется отдельно.
     *
     * @param null $repository
     * @param bool $refresh
     *
     * @return mixed
     * @throws Exception
     */
    public function initStorage($repository = null, $refresh = false)
    {
        if ($this->items && !$refresh) {
            $this->setIsFromCache(true);
            return $this->items;
        }

        $class = $this->getRepositoryClass();
        /** @var $repository BaseCachebleRepository */
        if (!$repository) $repository =  new $class(['limit' => 1000]);

        $this->setIsFromCache(false);

        if ($items = $repository->search()) {
            if ($repository->isCacheble()) {
                foreach ($items as $item) {
                    $hash_name = $repository->getHashName(true, false) . ':' . $item->getPrimaryKey();
                    $repository->setHashName($hash_name);
                    $repository->setToCache($item);
                }
            }
        }

        return $this->items = $items ?? [];
    }



    /**
     * Получить все кешированные элементы.
     *
     * @param array $params
     * @param bool $cache_to флаг кешировать ли результат
     *                            если он еще не кеширован
     * @return array|null
     * @throws Exception
     */
    public function getList(array $params = ['limit' => 500], bool $cache_to = true) : ?array
    {
        if ($this->items) return $this->items;

        $class = $this->getRepositoryClass();
        /** @var $repository BaseCachebleRepository */
        $repository =  new $class($params);
        if (!$repository instanceof BaseCachebleRepository)
            throw new \RuntimeException("Репозиторий должен наследовать класс BaseCachebleRepository");

        $repository->setCache($cache_to);
        $repository->getHashName(true, false);

        $this->setIsFromCache(true);

        $items = [];
        // если в сервисе разрешено использования кеширования - пытаемся получить из кеша
        if ($this->isUseCache()) $items = $repository->getFromCache(false, $this->get_from_cache_params);

        if (!$this->items = $items) $this->initStorage();
        if ($this->items) {
            $this->items = $this->sort($this->items);
            $this->updateHashtable();
        }

        return $this->items;
    }

    public function getHashByItem($item)
    {
        return md5($item->getPrimaryKey());
    }

    protected function updateHashtable()
    {
        if ($this->getItems()) {
            $this->hashtable = [];
            foreach ($this->getItems() as $key => $item) {
                $this->hashtable[$this->getHashByItem($item)] = $key;
            }
        }

        return true;
    }

    protected function sort(array $items)
    {
        if ($this->sorting) {
            foreach ($this->sorting as $key => $direction) {
                ArrayHelper::multisort($items, $key, $direction);
            }
        }

        return $items;
    }

    public function getByExp(array $attributes)
    {
        $result = [];
        if ($items = $this->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) continue;
                    if (preg_match("#$value#iu", $item->$name) == false) {
                        continue 2;
                    }
                }
                $result[] = $item;
            }
            // пересортировываем результат
            $result = $this->sort($result);
        }

        return $result;
    }

    public function getByMulti(array $attributes)
    {
        $result = [];
        if ($items = $this->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if ($value === false || is_null($value)) continue;
                    if ($item->$name !== $value) continue 2;
                }
                $result[] = $item;
            }
            // пересортировываем результат
            $result = $this->sort($result);
        }

        return $result;
    }

    public function getBy(array $attributes)
    {
        /** @var ActiveRecord $item */
        if ($items = $this->getList()) {
            foreach ($items as $item) {
                foreach ($attributes as $name => $value) {
                    if (!$item->hasAttribute($name)) continue 2;
                    if ($value === false || is_null($value)) continue;
                    if ($item->$name !== $value) continue 2;
                }
                return $item;
            }
        }

        return null;
    }

    public function getById(int $id, array $attributes = [])
    {
        if ($items = $this->getList()) {
            foreach ($items as $item) {
                if ($item->getPrimaryKey() == $id) {
                    if ($attributes) {
                        foreach ($attributes as $name => $value) {
                            if ($value === false || is_null($value)) continue;
                            if ($item->$name !== $value) continue 2;
                        }
                    }
                    return $item;
                }
            }
        }

        return null;
    }

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

    public function getByName(string $name, array $attributes = [])
    {
        $attributes = array_merge($attributes, ['name' => $name]);
        return $this->getBy($attributes) ?? null;
    }

    public function getByCode(string $code, array $attributes = [])
    {
        $attributes = array_merge($attributes, ['code' => $code]);
        return $this->getBy($attributes) ?? null;
    }

    public function getByKeyword(string $keyword, array $attributes = [])
    {
        $attributes = array_merge($attributes, ['keyword' => $keyword]);
        return $this->getBy($attributes) ?? null;
    }

    /**
     * Кеширование сущности по ID.
     * Если сущность уже есть в кеше - заменяем на актуальную.
     * Если нету - заносим актуальную.
     *
     * @param int $id
     * @return mixed|void
     */
    public function initById(int $id) :bool
    {
        return true;
    }
}