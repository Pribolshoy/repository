<?php

namespace pribolshoy\repository\frameworks\yii2\drivers;

class MysqlDriver extends BaseCacheDriver
{
    protected string $component = 'dbCache';

    public function get(string $key, array $params = [])
    {
        return \Yii::$app->{$this->component}->get($key);
    }

    public function set(string $key, $value, int $cache_duration = 0, array $params = []) :object
    {
        \Yii::$app->{$this->component}->set($key, $value);
        return $this;
    }

    public function delete(string $key, array $params = []) :object
    {
        return $this;
    }
}

