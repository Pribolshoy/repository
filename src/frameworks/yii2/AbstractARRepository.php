<?php

namespace pribolshoy\repository\frameworks\yii2;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\frameworks\yii2\helpers\ARHelper;
use pribolshoy\repository\traits\CatalogTrait;

/**
 * Class AbstractARRepository
 *
 * Абстрактный класс от которого наследуются
 * все конкретные реализации использующие в качестве модели/сущности
 * ActiveQuery
 *
 * @package pribolshoy\repository\frameworks\yii2
 */
abstract class AbstractARRepository extends AbstractCachebleRepository
{
    use ARHelper, CatalogTrait;

    protected ?string $driver_path = "\\pribolshoy\\repository\\frameworks\\yii2\\drivers\\";

    /**
     * @return $this
     * @throws \pribolshoy\exceptions\RepositoryException
     */
    protected function makeQueryBuilder()
    {
        $this->model = ($this->getModel())::find();
        return $this;
    }

    /**
     * Дополняется метод родительского класса.
     *
     * return $this
     */
    protected function beforeFetch()
    {
        $this->model->asArray($this->getIsArray());
        return parent::beforeFetch();
    }

    /**
     * В методе совершается выборка из БД в через уже
     * подготовленную и собранную модель.
     * Еще здесь происходит установка лимиа выборки и порядок
     *
     */
    protected function fetch(): object
    {
        $this->getTotal();
        // после получения полного списка элементов
        $this->addLimitAndOffset();

        if (isset($this->filter['single']) && $this->filter['single']) {
            $this->items = $this->model->one();
        } else {
            $this->items = $this->model->all();
        }

        return $this;
    }

    /**
     * В методе устанавливаются limit и offset
     * запроса перед выборкой.
     */
    protected function addLimitAndOffset() :object
    {
        if ($orderBy = $this->getOrderbyByFilter()) $this->model->orderBy($orderBy);
        $this->model
            ->limit($this->filter['limit'])
            ->offset($this->filter['offset'] ?? 0);

        return $this;
    }

    protected function getTotal()
    {
        if ($this->getNeedTotal()) {
            $pages = $this->initPages($this->model->count(), $this->filter['limit'])
                ->getPages();
            if ($this->existsFilter('page')) $pages->setPage($this->getFilters()['page'] - 1);
            if ($pages->offset) $this->filter['offset'] = $pages->offset;
        }
        return $this;
    }

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

    protected function getTableName(): string
    {
        return $this->getModel()::tableName();
    }

}

