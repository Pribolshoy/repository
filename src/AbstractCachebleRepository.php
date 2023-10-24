<?php

namespace pribolshoy\repository;

use pribolshoy\repository\interfaces\CacheDriverInterface;
use pribolshoy\repository\traits\CatalogTrait;

/**
 * Class AbstractRepository
 *
 * Abstract class for realization of searching specific entity.
 *
 * @package app\repositories
 */
abstract class AbstractCachebleRepository extends AbstractRepository
{
    use CatalogTrait;

    protected ?string $driver_path = '';

    protected ?CacheDriverInterface $driver_instance = null;

    /**
     * Активно ли кеширование.
     * Т.е. нужно ли кешировать элементы
     * @var bool
     */
    protected bool $active_cache = true;

    /**
     * Название компонтента используемого
     * для кеширования результата
     * @var string
     */
    protected ?string $driver = 'redis';

    /**
     * Название ключа по которому будет кешириваться результат
     */
    protected ?string $hash_name = null;

    /**
     * Длительность кеширования - 24 часа по умолчанию
     * Сделал 3 часа.
     * Может переопределяться в наследнике
     */
    protected int $cache_duration = 10801;

    /**
     * Максимальный номер страницы пагинации
     * который кешируется
     * @var integer
     */
    protected int $max_cached_page = 4;

    /**
     * @param int $num
     * @return $this
     */
    public function setMaxCachedPage($num)
    {
        $this->max_cached_page = $num;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxCachedPage() :int
    {
        return $this->max_cached_page;
    }

    /**
     * Возможно ли кешировать этот результат.
     * Т.к. не каждый результат целесообразно кешировать
     * делаем отдельный метод с проверками.
     *
     * Переопределяет родителя.
     *
     * @return bool
     */
    public function isCacheble() :bool
    {
        if ($this->isCacheActive()
            && $this->page <= $this->getMaxCachedPage()
            && $this->getHashName()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Получение объекта хранилища
     * Может переопределяться в наследнике.
     * По умолчанию используется Redis
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriver()
    {
        if ($this->driver_instance) return $this->driver_instance;

        if ($this->driver) {
            $class = $this->driver_path . ucfirst($this->driver) . 'Driver';
            if (class_exists($class)) {
                return $this->driver_instance = new $class();
            }
        }
        throw new \Exception('Не определен или не валидный Драйвер');
    }

    /**
     * Установка разрешения кеширования
     *
     * @param bool $activate
     *
     * @return $this
     */
    public function setCache(bool $activate = true)
    {
        $this->active_cache = $activate;
        return $this;
    }

    /**
     * Флаг активно ли кеширование.
     *
     * @return bool
     */
    public function isCacheActive(): bool
    {
        return $this->active_cache;
    }

    public function setCacheDuration(int $duration)
    {
        $this->cache_duration = $duration;
        return $this;
    }

    /**
     * Получение продолжительности кеширования
     * @return int
     */
    public function getCacheDuratuion(): int
    {
        return $this->cache_duration;
    }

    /**
     * Устанавливает название имени по которому
     * будет кешириваться результат
     *
     * @param string $hash_name
     * @return static
     */
    public function setHashName(string $hash_name)
    {
        $this->hash_name = $hash_name;
        return $this;
    }

    /**
     * Получить наименование кеша.
     *
     * @param bool $refresh
     * @param bool $use_params
     * @param bool $save_to
     *
     * @return string
     */
    public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true) :string
    {
        // если он уже задан и нет флага "обновить"
        if ($this->hash_name && !$refresh) {
            return $this->hash_name;
        } else {
            $hash_name = $this->getTableName();
            if ($use_params && $this->filter) {
                // таблица
                $hash_name = $hash_name . ':' . $this->getHashFromArray($this->getFilters());
            }
            if ($save_to) $this->hash_name = $hash_name = trim($hash_name, '&');
        }

        return $hash_name ?? '';
    }

    /**
     * Кешировать данные через заданный драйвер
     *
     * @param mixed $data данные для кеширования
     * @param array $params
     *
     * @return $this|array
     * @throws \Exception
     */
    public function setToCache($data, array $params = [])
    {
        if (!$this->getHashName()) return [];
        $this->getDriver()->set($this->getHashName(), $data, $this->getCacheDuratuion(), $params);
        return $this;
    }

    /**
     * Получить кешированные данные
     *
     * @param bool $refresh
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function getFromCache(bool $refresh = false, array $params = [])
    {
        if (!$this->getHashName($refresh)) return [];
        return $this->getDriver()->get($this->getHashName(), $params) ?? [];
    }

    /**
     * Удалить кешированные данные
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function deleteFromCache(array $params = [])
    {
        return $this->getDriver()->delete($this->getHashName(), $params) ?? [];
    }

    /**
     * Get string hash from array
     *
     * @param array $data
     * @param bool $hashToMd5
     * @return string
     */
    public function getHashFromArray(array $data, bool $hashToMd5 = false) :string
    {
        if ($data) $hash = json_encode(array_diff($data, [null]));
        if (strlen($hash) > 50 || $hashToMd5) $hash = md5($hash);

        return $hash ?? '';
    }
}

