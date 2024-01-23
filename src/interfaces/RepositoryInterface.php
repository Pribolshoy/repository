<?php

namespace pribolshoy\repository\interfaces;

interface RepositoryInterface
{

    /**
     * Set params property
     *
     * @param array $params
     * @param bool $update_filter flag - is to update filter properties
     * @param bool $clear_filter flag - is to clear filter properties before updating
     *
     * @return $this
     */
    public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object;

    /**
     * Get params property.
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Get filter property.
     *
     * @return array
     */
    public function getFilters(): array;

    public function getFilter(string $name);

    /**
     * Run search.
     *
     * @return bool|mixed
     */
    public function search();

    /**
     * Gets object by which repository will fetch rows.
     *
     * @return object
     */
    public function getModel(): object;
}

