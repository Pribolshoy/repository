<?php

namespace pribolshoy\repository\tests\services;

use PHPUnit\Framework\TestCase;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\PaginatedCachebleServiceInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\structures\HashtableStructure;

// Конкретный класс для тестирования абстрактного PaginatedCachebleService
class ConcretePaginatedCachebleService extends \pribolshoy\repository\services\PaginatedCachebleService
{
    public function sort(array $items): array { return $items; }
    public function getItemAttribute($item, string $name) { return $item[$name] ?? null; }
    public function getByHashtable($key, ?string $structureName = null) { return null; }
    public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
    public function getItemHash($item) { return $item['id'] ?? null; }
    public function hash($value): string { return (string)$value; }
    private $mockRepository = null;
    
    public function getRepository(array $params = []): RepositoryInterface {
        if ($this->mockRepository === null) {
            // Создаем простой мок без использования PHPUnit методов
            $this->mockRepository = new class implements CachebleRepositoryInterface {
                public function search() { return []; }
                public function isCacheble() { return true; }
                public function getHashPrefix() { return 'repo_'; }
                public function getFromCache($refresh = false, array $params = []) { return null; }
                public function setToCache($value, array $params = []) { return $this; }
                public function deleteFromCache(array $params = []): object { return $this; }
                public function setHashName(string $hash_name) { return $this; }
                public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
                public function setActiveCache(bool $active_cache) { return $this; }
                public function isCacheActive(): bool { return true; }
                public function setCacheDuration(int $cache_duration) { return $this; }
                public function getCacheDuration(): int { return 3600; }
                public function setParams(array $params, bool $merge = false, bool $reset_filters = false) { return $this; }
                public function getParams(): array { return []; }
                public function getFilters() { return []; }
                public function getFilter($name = null) { return null; }
                public function setNeedTotal(bool $need_total) { return $this; }
                public function getNeedTotal(): bool { return false; }
                public function setTotalCount(int $total_count) { return $this; }
                public function getTotalCount(): int { return 0; }
                public function setIsArray(bool $is_array) { return $this; }
                public function getIsArray(): bool { return true; }
                public function getTableName(): string { return 'test_table'; }
                public function makeQueryBuilder() { return $this; }
                public function fetch(): object { return new \stdClass(); }
                public function defaultFilter() {}
                public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
                public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                    return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                        public function get(string $key, array $params = []) { return null; }
                        public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                        public function delete(string $key, array $params = []): object { return $this; }
                    };
                }
                public function getMaxCachedPage(): int { return 4; }
                public function setMaxCachedPage($num): object { return $this; }
                public function getDriverParams(): array { return []; }
                public function getTotalHashPrefix(): string { return 'total_repo_'; }
                public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return md5(json_encode($data)); }
            };
        }
        return $this->mockRepository;
    }
    
    public function setMockRepository($repository) {
        $this->mockRepository = $repository;
    }
    public function getTableName(): string { return 'test_table'; }
    public function defaultFilter() {}
    public function fetch(): object { return $this->createMock(\stdClass::class); }
    public function prepareItem($item) { return $item; }
    public function getAliasAttribute(): string { return 'name'; }
    public function getList(array $params = [], bool $cache_to = true): ?array { return $this->getItems(); }
    public function collectItemsPrimaryKeys(array $items): array { return array_column($items, 'id'); }
    public function getByIds(array $ids, array $attributes = []): array {
        // Если метод getItemService не переопределен, вызовется исключение
        return parent::getByIds($ids, $attributes);
    }
    protected function getItemService(): \pribolshoy\repository\interfaces\BaseServiceInterface {
        // Переопределяем для тестирования getByIds
        if ($this->mockItemService === null) {
            $this->mockItemService = new class implements \pribolshoy\repository\interfaces\BaseServiceInterface {
                public function getByIds(array $ids, array $attributes = []): array { return []; }
                public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
                public function getItemStructure(bool $refresh = false): \pribolshoy\repository\interfaces\StructureInterface { return new \pribolshoy\repository\structures\ArrayStructure($this); }
                public function getBasicHashtableStructure(bool $refresh = false): \pribolshoy\repository\structures\HashtableStructure { return new \pribolshoy\repository\structures\HashtableStructure($this); }
                public function getNamedStructures(): array { return []; }
                public function getNamedStructure(string $name): ?\pribolshoy\repository\interfaces\StructureInterface { return null; }
                public function getItems(): ?array { return []; }
                public function setItems(array $items): void { $this->items = $items; }
                public function addItem($item, bool $replace_if_exists = true): object { return $this; }
                public function getItemHash($item): string { return md5(serialize($item)); }
                public function hash($value): string { return md5($value); }
                public function getHashtable(): array { return []; }
                public function updateHashtable(): object { return $this; }
                public function getFilter(bool $refresh = false): \pribolshoy\repository\interfaces\FilterInterface { return new class implements \pribolshoy\repository\interfaces\FilterInterface {
                    public function getList(array $params = [], bool $cache_to = true): ?array { return []; }
                    public function getByExp(array $attributes) { return []; }
                    public function getByMulti(array $attributes) { return []; }
                    public function getBy(array $attributes) { return null; }
                    public function getById(int $id, array $attributes = []) { return null; }
                    public function getByIds(array $ids, array $attributes = []): array { return []; }
                    public function filterByAttributes($item, array $attributes): bool { return true; }
                }; }
                public function setFilterClass(string $filter_class): object { return $this; }
                public function setSorting(array $sorting): object { return $this; }
                public function getList(array $params = [], bool $cache_to = true): ?array { return []; }
                public function getByExp(array $attributes): array { return []; }
                public function getByMulti(array $attributes): array { return []; }
                public function getBy(array $attributes) { return null; }
                public function getById(int $id, array $attributes = []) { return null; }
                public function setPrimaryKeys(array $primaryKeys): object { return $this; }
                public function isMultiplePrimaryKey(): bool { return true; }
                public function getRepository(array $params = []): \pribolshoy\repository\interfaces\RepositoryInterface { return new class implements \pribolshoy\repository\interfaces\RepositoryInterface {
                    public function search() { return []; }
                    public function getTableName(): string { return 'test'; }
                    public function makeQueryBuilder() { return $this; }
                    public function fetch(): object { return new \stdClass(); }
                    public function defaultFilter() {}
                    public function setParams(array $params, bool $merge = false, bool $reset_filters = false) { return $this; }
                    public function getParams(): array { return []; }
                    public function getFilters() { return []; }
                    public function getFilter($name = null) { return null; }
                    public function setNeedTotal(bool $need_total) { return $this; }
                    public function getNeedTotal(): bool { return false; }
                    public function setTotalCount(int $total_count) { return $this; }
                    public function getTotalCount(): int { return 0; }
                    public function setIsArray(bool $is_array) { return $this; }
                    public function getIsArray(): bool { return true; }
                }; }
                public function setRepositoryClass(string $repository_class): object { return $this; }
            };
        }
        return $this->mockItemService;
    }
    private $mockItemService = null;
    public function setMockItemService($service) {
        $this->mockItemService = $service;
    }
}

// Класс для тестирования исключения getItemService
class ConcretePaginatedCachebleServiceWithoutItemService extends \pribolshoy\repository\services\PaginatedCachebleService
{
    public function sort(array $items): array { return $items; }
    public function getItemAttribute($item, string $name) { return $item[$name] ?? null; }
    public function getByHashtable($key, ?string $structureName = null) { return null; }
    public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
    public function getItemHash($item) { return $item['id'] ?? null; }
    public function hash($value): string { return (string)$value; }
    public function getRepository(array $params = []): RepositoryInterface {
        return $this->createMock(RepositoryInterface::class);
    }
    public function getTableName(): string { return 'test_table'; }
    public function defaultFilter() {}
    public function fetch(): object { return $this->createMock(\stdClass::class); }
    public function prepareItem($item) { return $item; }
    public function getAliasAttribute(): string { return 'name'; }
    public function getList(array $params = [], bool $cache_to = true): ?array { return $this->getItems(); }
    public function collectItemsPrimaryKeys(array $items): array { return array_column($items, 'id'); }
    // Не переопределяем getItemService() - должен выбрасывать исключение
}

final class PaginatedCachebleServiceTest extends TestCase
{
    public function test_GetPaginationHashPrefix_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->getPaginationHashPrefix();

        // Assert
        $this->assertEquals('pagination:', $result);
    }

    public function test_GetHashPrefix_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->getHashPrefix();

        // Assert
        $this->assertEquals('list:', $result);
    }

    public function test_GetCacheParams_ReturnsDefaultParams()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->getCacheParams('get');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('strategy', $result);
        $this->assertEquals('getValue', $result['strategy']);
    }

    public function test_GetCacheParams_WithSet_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->getCacheParams('set');

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_GetFilter_ReturnsPaginatedServiceFilter()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $filter = $service->getFilter();

        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\filters\PaginatedServiceFilter::class, $filter);
    }

    public function test_InitStorage_ReturnsSelf()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $service->setMockRepository($repository);

        // Act
        $result = $service->initStorage($repository);

        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorage_WithNullRepository_ReturnsSelf()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->initStorage(null);

        // Assert
        $this->assertSame($service, $result);
    }

    public function test_ClearStorage_WithRepository_DeletesCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Ожидаем, что setHashName будет вызван дважды (для entities и pagination)
        $repository->expects($this->exactly(2))->method('setHashName');
        $repository->expects($this->exactly(2))->method('deleteFromCache');

        // Act
        $result = $service->clearStorage($repository);

        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_WithoutRepository_CreatesRepository()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        $service->setMockRepository($repository);

        // Act
        $result = $service->clearStorage(null, []);

        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_DeletesEntitiesAndPaginationCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Ожидаем, что setHashName будет вызван дважды (для entities и pagination)
        // Важно: ожидания должны быть установлены ДО вызова метода
        $repository->expects($this->exactly(2))->method('setHashName');
        $repository->expects($this->exactly(2))->method('deleteFromCache');

        // Act
        $result = $service->clearStorage($repository);

        // Assert
        $this->assertTrue($result);
    }

    public function test_GetByIds_WhenItemServiceOverridden_ReturnsItems()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();

        // Act
        $result = $service->getByIds([1, 2]);

        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetItemService_WhenNotOverridden_ThrowsException()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleServiceWithoutItemService();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getItemService');
        $method->setAccessible(true);

        // Assert
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Method ' . \pribolshoy\repository\services\PaginatedCachebleService::class . '::getItemService must be overridden');

        // Act
        $method->invoke($service);
    }

    public function test_GetByIds_WhenItemServiceNotOverridden_ThrowsException()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleServiceWithoutItemService();

        // Assert
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Method ' . \pribolshoy\repository\services\PaginatedCachebleService::class . '::getItemService must be overridden');

        // Act
        $service->getByIds([1, 2]);
    }
}

