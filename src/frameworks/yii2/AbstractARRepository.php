<?php

namespace pribolshoy\repository\frameworks\yii2;

use pribolshoy\repository\AbstractRepository;

/**
 * Class AbstractARRepository
 *
 * Абстрактный класс от которого наследуются
 * все конкретные реализации исполюзующие в качестве модели/сущности
 * ActiveQuery
 *
 * @package pribolshoy\repository\frameworks\yii2
 */
abstract class AbstractARRepository extends AbstractRepository
{
    /**
     * AbstractARRepository constructor.
     * Дополняется конструктор базового абстрактного класса.
     *
     * @param array $params
     * @param null $model_class
     * @throws \pribolshoy\exceptions\RepositoryException
     */
    public function __construct($params = [], $model_class = null)
    {
        parent::__construct($params, $model_class);
        $this->makeModel();
        $this->collectFilter();
    }

    /**
     * Инициация объекта self::model через который будет
     * происходить поиск.
     * Он должен быть подготовлен так, чтобы им сходу можно
     * было пользоваться, конфигурировать.
     *
     * @return $this
     * @throws \pribolshoy\exceptions\RepositoryException
     */
    protected function makeModel()
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
        $this->addQueries();
        return parent::beforeFetch();
    }

    protected function fetch(): void
    {
        if (isset($this->filter['single']) && $this->filter['single']) {
            $this->items = $this->model->one();
        } else {
            $this->items = $this->model->all();
        }
    }

    /**
     * Дополняется метод родительского класса.
     *
     * @return $this
     */
    protected function collectFilter()
    {
        parent::collectFilter();
        $this->addPreQueries();
        return $this;
    }

    /**
     * В методе происходит подготовка параметров для
     * выборки where, но которые должны быть получены
     * с помощью предварительных выборок.
     *
     * Например, если для выборки необходимы данные
     * которые можно получить только одним или несколькими
     * отдельными запросами, то они складываются в этот метод.
     *
     * return $this
     */
    protected function addPreQueries() {return $this;}

    /**
     * В методе происходит установка всех параметров where
     * которые будут в запросе
     *
     * return $this
     */
    protected function addQueries()  {return $this;}

    /**
     * В методе устанавливаются внешние связи через
     * метод with()
     *
     * return $this
     */
    protected function addConnections() {return $this;}

    /**
     * В методе устанавливаются limit и offset
     * запроса перед выборкой.
     *
     * return $this
     */
    protected function addLimitAndOffset() {return $this;}

    /**
     *
     *
     * @return mixed
     *
     * return $this
     */
    protected function getTotal() {return $this;}
}

