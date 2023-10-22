<?php

namespace pribolshoy\repository\traits;

/**
 * Trait CachebleCatalogRepositoryTrait
 *
 * Трейт дополняющий базовый трейт CachebleRepositoryTrait для кеширования
 * и CatalogTrait для каталогизации элементов.
 * Создана для упрощения вытаскивания кешированных элементов
 * сущностей и постраничной разбивки
 *
 * @package app\components\common\traits
 */
trait CachebleCatalogTrait
{
    use CachebleTrait,
        CatalogTrait;

    /**
     * @var integer Максимальный номер страницы пагинации
     * который кешируется
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
     * @throws Exception
     */
    public function isCacheble() :bool
    {
        if ($this->isCacheActive() && $this->page <= $this->getMaxCachedPage() && $this->getHashName()) {
            return true;
        }
        return false;
    }
}