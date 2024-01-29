<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\AbstractCachebleRepository;

interface CachebleServiceInterface extends ServiceInterface
{
    public function isUseCache(): bool;

    public function setUseCache(bool $use_cache);

    public function isCacheExists(?AbstractCachebleRepository $repository = null);

    public function initStorageEvent(): bool;

    public function setHashPrefix(string $hash_prefix): self;

    public function getHashPrefix(): string;

    public function isFromCache(): bool;

    public function setIsFromCache(bool $is_from_cache);

    public function getFetchingStep(): int;

    public function setFetchingStep(int $fetching_step): self;

    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false);

    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = []);

    public function refreshItem(array $primaryKeyArray);

    public function getById(int $id, array $attributes = []);

    public function getByIds(array $ids, array $attributes = []);

}

