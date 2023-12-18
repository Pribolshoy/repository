<?php

namespace pribolshoy\repository\drivers;

use pribolshoy\repository\interfaces\CacheDriverInterface;

abstract class AbstractCacheDriver implements CacheDriverInterface
{
    protected string $component = 'redis';

    public function __construct(array $params = [])
    {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    abstract public function get(string $key, array $params = []);

    abstract public function set(string $key, $value, int $cache_duration = 0, array $params = []) :object;

    abstract public function delete(string $key, array $params = []) :object;
}

