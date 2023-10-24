<?php

namespace pribolshoy\repository\frameworks\yii2\drivers;

class FileDriver extends BaseCacheDriver
{
    protected string $component = 'cache';

    public function get(string $key, array $params = [])
    {
        \Yii::$app->{$this->component}->get($key);
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

