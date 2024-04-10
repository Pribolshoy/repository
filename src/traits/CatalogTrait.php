<?php

namespace pribolshoy\repository\traits;

use yii\data\Pagination;

/**
 * Trait CatalogTrait
 *
 * Трейт добавляющий функционал для взаимодействия
 * со списком элементов нуждающихся в пагинации.
 *
 * @package app\components\common\traits
 */
trait CatalogTrait
{
    protected string $pagination_class = 'yii\\data\\Pagination';

    /**
     * Номер актуальной страницы каталога
     * @var int
     */
    public int $page = 0;

    /**
     * Объект Pagination
     * @var
     */
    public $pages;

    /**
     * Задает параметр pages объектом Pagination
     *
     * @param $pages
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * Возвращает параметр pages с объектом Pagination
     *
     * @return Pagination
     */
    public function getPages()
    {
        return $this->pages;
    }
}