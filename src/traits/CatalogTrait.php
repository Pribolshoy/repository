<?php

namespace pribolshoy\repository\traits;

use yii\data\Pagination;

/**
 * Trait CatalogTrait
 *
 * Трейт добавляющий функционал для взаимодействия
 * со списком элементов нуждающимся в пагинации.
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
    public int $page = 1;

    /**
     * Объект Pagination
     * @var
     */
    public $pages;

    /**
     * @var int Количество выбранных items
     */
    public ?int $total = 0;

    /**
     * @var boolean Нужно ли получать количество всех строк
     */
    public bool $need_total = true;

    /**
     * Задание пагинации
     *
     * @param int $totalCount число количество элементов выборки
     * @param int $pageSize количество элементов на странице
     * @param bool $pageSizeParam
     *
     * @return static
     */
    public function initPages(int $totalCount, int $pageSize, bool $pageSizeParam = false)
    {
        $pagination_class = $this->pagination_class;
        // подключаем класс Pagination, выводим по $data['limit'] продуктов на страницу
        $pages = new $pagination_class(['totalCount' => $totalCount, 'pageSize' => $pageSize]);
        // приводим параметры в ссылке к ЧПУ
        $pages->pageSizeParam = $pageSizeParam;
        $this->setPages($pages);

        return $this;
    }

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

    /**
     * Установка значения выборки в виде массива
     * @param $need_total
     * @return $this
     */
    public function setNeedTotal($need_total)
    {
        $this->need_total = $need_total;

        return $this;
    }

    /**
     * Получение значения выборки всего количества элементов
     * @return bool
     */
    public function getNeedTotal(): bool
    {
        return $this->need_total;
    }
}