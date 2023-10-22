<?php

namespace pribolshoy\repository\traits;

use pribolshoy\repository\interfaces\CacheDriverInterface;

/**
 * Trait CachebleRepositoryTrait
 * Трейт для предоставления функционала кеширования.
 * Для кеширования использует драйвера которые назначаются в
 * реализующем классе.
 * Реализует базовый функционал для кеширования, без излишеств.
 *
 * @package app\components\common\traits
 */
trait CachebleTrait
{
    /**
     * Активно ли кеширование.
     * Т.е. нужно ли кешировать элементы
     * @var bool
     */
    protected bool $active_cache = true;

    protected ?string $driver_path = null;

    protected ?CacheDriverInterface $driver_instance = null;

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
     * Получение названия имени по которому
     * будет кешириваться результат.
     * Реализация может переписываться в потомке.
     *
     * @param bool $refresh - флаг перерасчета хэша кеша
     * @return mixed
     * @throws Exception
     */
    public function getHashName(bool $refresh = false) :string
    {
        if ($this->hash_name) return $this->hash_name;
        throw new Exception('Hash name не определено');
    }

    /**
     * Кешировать данные через заданный драйвер
     *
     * @param mixed $data данные для кеширования
     * @param array $params
     *
     * @return $this|array
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function deleteFromCache(array $params = [])
    {
        return $this->getDriver()->delete($this->getHashName(), $params) ?? [];
    }

    /**
     * Удаление данных в Хранилище / Кеш
     * Переопределяется в наследнике
     *
     * @return mixed
     */
    public function clearStorage()
    {
        return $this;
    }

    /**
     * Возможно ли кешировать этот результат.
     *
     * @return bool
     */
    public function isCacheble() :bool
    {
        if ($this->isCacheActive()) {
            return true;
        }
        return false;
    }
}