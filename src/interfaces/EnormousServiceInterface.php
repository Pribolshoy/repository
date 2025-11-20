<?php

namespace pribolshoy\repository\interfaces;

interface EnormousServiceInterface extends CachebleServiceInterface
{
    /**
     * Get maximum initialization iteration count.
     *
     * @return int
     */
    public function getMaxInitIteration(): int;

    /**
     * Set initialization iteration count.
     *
     * @param int|null $init_iteration Iteration count
     * @return EnormousServiceInterface
     */
    public function setInitIteration(?int $init_iteration): EnormousServiceInterface;

    /**
     * Get initialization iteration count.
     *
     * @return int|null
     */
    public function getInitIteration(): ?int;

    /**
     * Set fetching flag.
     *
     * @param bool $is_fetching Whether fetching is in progress
     * @return EnormousServiceInterface
     */
    public function setIsFetching(bool $is_fetching): EnormousServiceInterface;

    /**
     * Check if fetching is in progress.
     *
     * @return bool
     */
    public function isFetching();
}

