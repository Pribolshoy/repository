<?php

namespace pribolshoy\repository\interfaces;

interface CacheDriverInterface
{
    /**
     * Get value from cache.
     *
     * @param string $key Cache key
     * @param array $params Additional parameters
     * @return mixed
     */
    public function get(string $key, array $params = []);

    /**
     * Set value to cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $cache_duration Cache duration in seconds (0 for default)
     * @param array $params Additional parameters
     * @return object
     */
    public function set(string $key, $value, int $cache_duration = 0, array $params = []) :object;

    /**
     * Delete value from cache.
     *
     * @param string $key Cache key
     * @param array $params Additional parameters
     * @return object
     */
    public function delete(string $key, array $params = []) :object;
}

