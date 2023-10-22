<?php

namespace pribolshoy\repository\interfaces;

interface CacheDriverInterface
{
    public function get(string $key, array $params = []);

    public function set(string $key, $value, int $cache_duration = 0, array $params = []) :object;

    public function delete(string $key, array $params = []) :object;
}

