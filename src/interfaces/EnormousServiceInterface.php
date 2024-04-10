<?php

namespace pribolshoy\repository\interfaces;

interface EnormousServiceInterface extends CachebleServiceInterface
{
    public function getMaxInitIteration(): int;

    public function setInitIteration(?int $init_iteration): object;

    public function getInitIteration(): ?int;

    public function setIsFetching(bool $is_fetching): object;

    public function isFetching();
}

