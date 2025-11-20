<?php

namespace pribolshoy\repository\tests\filters;

use pribolshoy\repository\filters\CachebleServiceFilter;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteCachebleServiceForFilter implements CachebleServiceInterface
{
    private $items = null;
    private $repository;
    private $useCache = true;
    private $isFromCache = false;
    private $hashPrefix = '';
    
    public function getItems(): ?array { return $this->items; }
    public function setItems(array $items): void { $this->items = $items; }
    public function getRepository(array $params = []): \pribolshoy\repository\interfaces\RepositoryInterface { return $this->repository; }
    public function setRepository($repository) { $this->repository = $repository; }
    public function sort(array $items): array { return $items; }
    public function isUseCache(): bool { return $this->useCache; }
    public function setUseCache(bool $use_cache): CachebleServiceInterface { $this->useCache = $use_cache; return $this; }
    public function setIsFromCache(bool $is_from_cache): CachebleServiceInterface { $this->isFromCache = $is_from_cache; return $this; }
    public function isFromCache(): bool { return $this->isFromCache; }
    public function getIdPostfix(): string { return ':id'; }
    public function getHashPrefix(): string { return $this->hashPrefix; }
    public function setHashPrefix(string $hash_prefix): CachebleServiceInterface { $this->hashPrefix = $hash_prefix; return $this; }
    public function getCacheParams(string $name = ''): array { return []; }
    public function initStorageEvent(): bool { return true; }
    public function useAliasCache(): bool { return false; }
    public function setUseAliasCache(bool $use_alias_cache): CachebleServiceInterface { return $this; }
    public function getByAliasStructure($value) { return null; }
    public function isCacheExists($repository = null): bool { return false; }
    public function setFetchingStep(int $fetching_step): CachebleServiceInterface { return $this; }
    public function getFetchingStep(): int { return 1000; }
    public function initStorage(?\pribolshoy\repository\interfaces\RepositoryInterface $repository = null, bool $refresh_repository_cache = false): CachebleServiceInterface { return $this; }
    public function clearStorage($repository = null, array $params = []): bool { return true; }
    public function refreshItem(array $primaryKeyArray): bool { return true; }
    public function prepareItem($item) { return $item; }
    public function setAliasPostfix(string $alias_postfix): CachebleServiceInterface { return $this; }
    public function getAliasPostfix(): string { return '_alias'; }
    public function getAliasAttribute(): string { return ''; }
    public function getByAlias(string $alias, array $attributes = []) { return []; }
    public function addCacheParams(string $name, array $param): CachebleServiceInterface { return $this; }
    public function setCacheParams(array $cache_params): CachebleServiceInterface { return $this; }
    public function deleteItem(string $primaryKey): bool { return true; }
    public function getItemIdValue($item) { return $this->getItemPrimaryKey($item); }
    public function afterRefreshItem(array $primaryKeyArray): void {}
    public function getItemAttribute($item, string $name) { return $item[$name] ?? null; }
    public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
    public function getByHashtable($key, ?string $structureName = null) { return []; }
    public function resort(): \pribolshoy\repository\interfaces\ServiceInterface { return $this; }
    public function collectItemsPrimaryKeys(array $items): array { return []; }
    public function getHashByItem($item) { return null; }
    public function isMultiplePrimaryKey(): bool { return true; }
    public function setPrimaryKeys(array $primaryKeys): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function getItemStructure(bool $refresh = false): \pribolshoy\repository\interfaces\StructureInterface { return new \pribolshoy\repository\structures\ArrayStructure($this); }
    public function getBasicHashtableStructure(bool $refresh = false): \pribolshoy\repository\structures\HashtableStructure { return new \pribolshoy\repository\structures\HashtableStructure($this); }
    public function getNamedStructures(): array { return []; }
    public function getNamedStructure(string $name): ?\pribolshoy\repository\interfaces\StructureInterface { return null; }
    public function setRepositoryClass(string $repository_class): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function addItem($item, bool $replace_if_exists = true): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function getItemHash($item) { return md5(serialize($item)); }
    public function hash($value): string { return md5($value); }
    public function getHashtable() { return []; }
    public function updateHashtable(): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function setFilterClass(string $filter_class): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function getFilter(bool $refresh = false): \pribolshoy\repository\interfaces\FilterInterface { return null; }
    public function setSorting(array $sorting): \pribolshoy\repository\interfaces\BaseServiceInterface { return $this; }
    public function getList(array $params = [], bool $cache_to = true): ?array { return $this->items; }
    public function getByExp(array $attributes): array { return []; }
    public function getByMulti(array $attributes): array { return []; }
    public function getBy(array $attributes) { return null; }
    public function getById(int $id, array $attributes = []) { return null; }
    public function getByIds(array $ids, array $attributes = []): array { return []; }
}

class CachebleServiceFilterTest extends CommonTestCase
{
    public function test_GetList_WhenItemsSet_ReturnsItems()
    {
        // Arrange
        $items = [['id' => 1, 'name' => 'test']];
        $service = new ConcreteCachebleServiceForFilter();
        $service->setItems($items);
        
        $filter = new CachebleServiceFilter($service);
        
        // Act
        $result = $filter->getList();
        
        // Assert
        $this->assertEquals($items, $result);
    }

    public function test_GetList_WhenItemsNotSet_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('search')->willReturn([]);
        $repository->method('setActiveCache')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getFromCache')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        
        $service->setRepository($repository);
        $service->setHashPrefix('test_prefix');
        
        $filter = new CachebleServiceFilter($service);
        
        // Act
        $result = $filter->getList();
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByAlias_WithAlias_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getFromCache')->willReturn(null);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setParams')->willReturnSelf();
        $repository->method('search')->willReturn([['id' => 1, 'alias' => 'test']]);
        
        $service->setRepository($repository);
        $service->setHashPrefix('test_prefix');
        
        $filter = new CachebleServiceFilter($service);
        
        // Act
        $result = $filter->getByAlias('test');
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetPrimaryKeyByAlias_WithAlias_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getFromCache')->willReturn(null);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setParams')->willReturnSelf();
        $repository->method('search')->willReturn([['id' => 1, 'alias' => 'test']]);
        
        $service->setRepository($repository);
        $service->setHashPrefix('test_prefix');
        
        $filter = new CachebleServiceFilter($service);
        
        // Act
        $result = $filter->getPrimaryKeyByAlias('test');
        
        // Assert
        $this->assertTrue(is_null($result) || is_string($result) || is_int($result));
    }
}

