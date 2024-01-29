<?php

namespace pribolshoy\repository\interfaces;

interface CachebleRepositoryInterface extends RepositoryInterface
{

    public function isCacheble() :bool;

    public function setActiveCache(bool $activate = true): object;
}

