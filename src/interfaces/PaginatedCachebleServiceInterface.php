<?php

namespace pribolshoy\repository\interfaces;

interface PaginatedCachebleServiceInterface extends CachebleServiceInterface
{
    /**
     * Get pagination hash prefix.
     *
     * @return string
     */
    public function getPaginationHashPrefix(): string;
}

