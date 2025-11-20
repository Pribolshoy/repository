<?php

namespace pribolshoy\repository\interfaces;

interface RepositoryInterface
{
    /**
     * Get total count of items.
     *
     * @return int|null
     */
    public function getTotalCount(): ?int;

    /**
     * Set params property
     *
     * @param array $params
     * @param bool $update_filter flag - is to update filter properties
     * @param bool $clear_filter flag - is to clear filter properties before updating
     *
     * @return RepositoryInterface
     */
    public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): RepositoryInterface;

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

    /**
     * Get filter by name.
     *
     * @param string $name Filter name
     * @return mixed
     */
    public function getFilter(string $name);

    /**
     * Run search.
     *
     * @return bool|mixed
     */
    public function search();

    /**
     * Gets query builder instance by which repository will fetch rows.
     *
     * Instance is created once and cached. Use force flag to recreate it.
     *
     * @param bool $force Force recreation of instance even if cached
     * @return object
     */
    public function getQueryBuilderInstance(bool $force = false): object;
}

