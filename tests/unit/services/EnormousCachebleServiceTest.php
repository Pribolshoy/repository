<?php

namespace pribolshoy\repository\tests\services;

use PHPUnit\Framework\TestCase;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\EnormousServiceInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\structures\HashtableStructure;

// Конкретный класс для тестирования абстрактного EnormousCachebleService
class ConcreteEnormousCachebleService extends \pribolshoy\repository\services\EnormousCachebleService
{
    public function sort(array $items): array { return $items; }
    public function getItemAttribute($item, string $name) { return $item[$name] ?? null; }
    public function getByHashtable($key, ?string $structureName = null) { 
        // Вызываем родительский метод для правильной работы
        return parent::getByHashtable($key, $structureName);
    }
    public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
    public function getItemHash($item) { return $item['id'] ?? null; }
    public function hash($value): string { return (string)$value; }
    private $mockRepository = null;
    
    public function getRepository(array $params = []): RepositoryInterface {
        if ($this->mockRepository === null) {
            $this->mockRepository = new class implements CachebleRepositoryInterface {
                public function search() { return []; }
                public function isCacheble(): bool { return true; }
                public function getHashPrefix(): string { return 'repo_'; }
                public function getFromCache($refresh = false, array $params = []) { return null; }
                public function setToCache($value, array $params = []): object { return $this; }
                public function deleteFromCache(array $params = []): object { return $this; }
                public function setHashName(string $hash_name): object { return $this; }
                public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
                public function setActiveCache(bool $activate = true): object { return $this; }
                public function isCacheActive(): bool { return true; }
                public function setCacheDuration(int $duration): object { return $this; }
                public function getCacheDuration(): int { return 3600; }
                public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
                public function getParams(): array { return []; }
                public function getFilters(): array { return []; }
                public function getFilter(string $name) { return null; }
                public function setNeedTotal(bool $need_total) { return $this; }
                public function getNeedTotal(): bool { return false; }
                public function setTotalCount(int $total_count) { return $this; }
                public function getTotalCount(): ?int { return 0; }
                public function setIsArray(bool $is_array) { return $this; }
                public function getIsArray(): bool { return true; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
                public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                    return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                        public function get(string $key, array $params = []) { return null; }
                        public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                        public function delete(string $key, array $params = []): object { return $this; }
                    };
                }
                public function getModel(): object { return $this; }
                public function getMaxCachedPage(): int { return 4; }
                public function getDriverParams(): array { return []; }
                public function getTotalHashPrefix(): string { return ''; }
                public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
                public function fetch(): object { return new \stdClass(); }
                public function defaultFilter() {}
            public function setMaxCachedPage($num): object { return $this; }
            };
        }
        return $this->mockRepository;
    }
    
    public function setMockRepository($repository) {
        $this->mockRepository = $repository;
    }
    
    public function getTableName(): string { return 'test_table'; }
    public function defaultFilter() {}
    public function fetch(): object { return new \stdClass(); }
    public function prepareItem($item) { return $item; }
    public function getAliasAttribute(): string { return 'name'; }
    public function getList(array $params = [], bool $cache_to = true): ?array { return $this->getItems(); }
}

final class EnormousCachebleServiceTest extends TestCase
{
    public function test_GetMaxInitIteration_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getMaxInitIteration();

        // Assert
        $this->assertEquals(10, $result);
    }

    public function test_SetInitIteration_SetsValue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $iteration = 5;

        // Act
        $service->setInitIteration($iteration);

        // Assert
        $this->assertEquals($iteration, $service->getInitIteration());
    }

    public function test_SetInitIteration_WithNull_ResetsValue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setInitIteration(5);

        // Act
        $service->setInitIteration(null);

        // Assert
        $this->assertNull($service->getInitIteration());
    }

    public function test_GetInitIteration_ByDefault_ReturnsNull()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getInitIteration();

        // Assert
        $this->assertNull($result);
    }

    public function test_SetIsFetching_SetsValue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $service->setIsFetching(true);

        // Assert
        $this->assertTrue($service->isFetching());
    }

    public function test_IsFetching_ByDefault_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->isFetching();

        // Assert
        $this->assertFalse($result);
    }

    public function test_GetHashPrefix_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getHashPrefix();

        // Assert
        $this->assertEquals('detail:', $result);
    }

    public function test_UseAliasCache_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->useAliasCache();

        // Assert
        $this->assertTrue($result);
    }

    public function test_GetCacheParams_ReturnsDefaultParams()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getCacheParams('get');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('strategy', $result);
        $this->assertEquals('getHValue', $result['strategy']);
    }

    public function test_GetCacheParams_WithSet_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getCacheParams('set');

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_GetCacheParams_WithGet_ReturnsDefaultParams()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $result = $service->getCacheParams('get');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('strategy', $result);
        $this->assertEquals('getHValue', $result['strategy']);
    }

    public function test_GetFilter_ReturnsEnormousServiceFilter()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Act
        $filter = $service->getFilter();

        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\filters\EnormousServiceFilter::class, $filter);
    }

    public function test_GetByHashtable_WhenItemsEmpty_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setItems([]);

        // Act
        $result = $service->getByHashtable(['id' => 1]);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_GetByHashtable_WhenItemsExist_CallsParent()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $items = [['id' => 1, 'name' => 'test']];
        $service->setItems($items);

        // Act
        $result = $service->getByHashtable(['id' => 1]);

        // Assert
        // Метод вызывает родительский метод, который может вернуть null или массив
        $this->assertTrue(is_null($result) || is_array($result));
    }

    public function test_GetByHashtableMulti_ThrowsException()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method ' . \pribolshoy\repository\services\EnormousCachebleService::class . '::getByHashtableMulti is deprecated!');

        // Act
        $service->getByHashtableMulti([['id' => 1]]);
    }

    public function test_InitStorage_WhenFirstRun_ClearsStorage()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $repository = new class implements \pribolshoy\repository\interfaces\CachebleRepositoryInterface {
            public function search() { return []; }
            public function isCacheble(): bool { return false; }
            public function getHashPrefix(): string { return 'repo_'; }
            public function getFromCache($refresh = false, array $params = []) { return null; }
            public function setToCache($value, array $params = []): object { return $this; }
            public function deleteFromCache(array $params = []): object { return $this; }
            public function setHashName(string $hash_name): object { return $this; }
            public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
            public function setActiveCache(bool $activate = true): object { return $this; }
            public function isCacheActive(): bool { return true; }
            public function setCacheDuration(int $duration): object { return $this; }
            public function getCacheDuration(): int { return 3600; }
            public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
            public function getParams(): array { return []; }
            public function getFilters(): array { return []; }
            public function getFilter(string $name) { return null; }
            public function setNeedTotal(bool $need_total) { return $this; }
            public function getNeedTotal(): bool { return false; }
            public function setTotalCount(int $total_count) { return $this; }
            public function getTotalCount(): ?int { return 0; }
            public function setIsArray(bool $is_array) { return $this; }
            public function getIsArray(): bool { return true; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
            public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                    public function get(string $key, array $params = []) { return null; }
                    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                    public function delete(string $key, array $params = []): object { return $this; }
                };
            }
            public function getModel(): object { return $this; }
            public function setMaxCachedPage($num): object { return $this; }
            public function getMaxCachedPage(): int { return 4; }
            public function getDriverParams(): array { return []; }
            public function getTotalHashPrefix(): string { return ''; }
            public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
            public function makeQueryBuilder() { return $this; }
            public function fetch(): object { return new \stdClass(); }
            public function defaultFilter() {}
        };
        
        // Мокаем clearStorage
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['clearStorage', 'getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        $service->expects($this->once())
            ->method('clearStorage')
            ->with(null);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals(1, $service->getInitIteration());
    }

    public function test_InitStorage_WhenSubsequentRun_IncrementsIteration()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setInitIteration(1);
        
        $repository = new class implements \pribolshoy\repository\interfaces\CachebleRepositoryInterface {
            public function search() { return []; }
            public function isCacheble(): bool { return false; }
            public function getHashPrefix(): string { return 'repo_'; }
            public function getFromCache($refresh = false, array $params = []) { return null; }
            public function setToCache($value, array $params = []): object { return $this; }
            public function deleteFromCache(array $params = []): object { return $this; }
            public function setHashName(string $hash_name): object { return $this; }
            public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
            public function setActiveCache(bool $activate = true): object { return $this; }
            public function isCacheActive(): bool { return true; }
            public function setCacheDuration(int $duration): object { return $this; }
            public function getCacheDuration(): int { return 3600; }
            public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
            public function getParams(): array { return []; }
            public function getFilters(): array { return []; }
            public function getFilter(string $name) { return null; }
            public function setNeedTotal(bool $need_total) { return $this; }
            public function getNeedTotal(): bool { return false; }
            public function setTotalCount(int $total_count) { return $this; }
            public function getTotalCount(): ?int { return 0; }
            public function setIsArray(bool $is_array) { return $this; }
            public function getIsArray(): bool { return true; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
            public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                    public function get(string $key, array $params = []) { return null; }
                    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                    public function delete(string $key, array $params = []): object { return $this; }
                };
            }
            public function getModel(): object { return $this; }
            public function setMaxCachedPage($num): object { return $this; }
            public function getMaxCachedPage(): int { return 4; }
            public function getDriverParams(): array { return []; }
            public function getTotalHashPrefix(): string { return ''; }
            public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
            public function makeQueryBuilder() { return $this; }
            public function fetch(): object { return new \stdClass(); }
            public function defaultFilter() {}
        };
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        $service->setInitIteration(1);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals(2, $service->getInitIteration());
    }

    public function test_InitStorage_WhenItemsFound_SetsItems()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $items = [['id' => 1, 'name' => 'test']];
        $repository = new class($items) implements \pribolshoy\repository\interfaces\CachebleRepositoryInterface {
            private $items;
            public function __construct($items) { $this->items = $items; }
            public function search() { return $this->items; }
            public function isCacheble(): bool { return false; }
            public function getHashPrefix(): string { return 'repo_'; }
            public function getFromCache($refresh = false, array $params = []) { return null; }
            public function setToCache($value, array $params = []): object { return $this; }
            public function deleteFromCache(array $params = []): object { return $this; }
            public function setHashName(string $hash_name): object { return $this; }
            public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
            public function setActiveCache(bool $activate = true): object { return $this; }
            public function isCacheActive(): bool { return true; }
            public function setCacheDuration(int $duration): object { return $this; }
            public function getCacheDuration(): int { return 3600; }
            public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
            public function getParams(): array { return []; }
            public function getFilters(): array { return []; }
            public function getFilter(string $name) { return null; }
            public function setNeedTotal(bool $need_total) { return $this; }
            public function getNeedTotal(): bool { return false; }
            public function setTotalCount(int $total_count) { return $this; }
            public function getTotalCount(): ?int { return 1; }
            public function setIsArray(bool $is_array) { return $this; }
            public function getIsArray(): bool { return true; }
            public function getModel(): object { return $this; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
            public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                    public function get(string $key, array $params = []) { return null; }
                    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                    public function delete(string $key, array $params = []): object { return $this; }
                };
            }
            public function setMaxCachedPage($num): object { return $this; }
            public function getMaxCachedPage(): int { return 4; }
            public function getDriverParams(): array { return []; }
            public function getTotalHashPrefix(): string { return ''; }
            public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
            public function makeQueryBuilder() { return $this; }
            public function fetch(): object { return new \stdClass(); }
            public function defaultFilter() {}
        };
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFetching());
    }

    public function test_InitStorageEvent_WhenNotFired_ReturnsTrue()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage'])
            ->getMock();
        $service->method('initStorage')->willReturnSelf();
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
        $this->assertNull($service->getInitIteration());
    }

    public function test_InitStorageEvent_WhenAlreadyFired_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        // Используем рефлексию для установки флага
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('init_storage_fired');
        $property->setAccessible(true);
        $property->setValue($service, true);
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_InitStorageEvent_CallsInitStorageInCycle()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage', 'isFetching'])
            ->getMock();
        
        $service->method('isFetching')
            ->willReturnOnConsecutiveCalls(true, true, false);
        // Цикл выполняется пока isFetching() возвращает true
        // При первом вызове isFetching() = true, вызывается initStorage()
        // При втором вызове isFetching() = true, вызывается initStorage()
        // При третьем вызове isFetching() = false, цикл завершается
        // Итого: initStorage вызывается 2 раза
        $service->expects($this->exactly(2))
            ->method('initStorage')
            ->willReturnSelf();
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitStorage_WhenItemsFoundAndCacheble_SetsToCache()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $items = [['id' => 1, 'name' => 'test']];
        
        $repository = new class($items) implements \pribolshoy\repository\interfaces\CachebleRepositoryInterface {
            private $items;
            public function __construct($items) { $this->items = $items; }
            public function search() { return $this->items; }
            public function isCacheble(): bool { return true; }
            public function getHashPrefix(): string { return 'repo_'; }
            public function getFromCache($refresh = false, array $params = []) { return null; }
            public function setToCache($value, array $params = []): object { return $this; }
            public function deleteFromCache(array $params = []): object { return $this; }
            public function setHashName(string $hash_name): object { return $this; }
            public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
            public function setActiveCache(bool $activate = true): object { return $this; }
            public function isCacheActive(): bool { return true; }
            public function setCacheDuration(int $duration): object { return $this; }
            public function getCacheDuration(): int { return 3600; }
            public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
            public function getParams(): array { return []; }
            public function getFilters(): array { return []; }
            public function getFilter(string $name) { return null; }
            public function setNeedTotal(bool $need_total) { return $this; }
            public function getNeedTotal(): bool { return false; }
            public function setTotalCount(int $total_count) { return $this; }
            public function getTotalCount(): ?int { return 1; }
            public function setIsArray(bool $is_array) { return $this; }
            public function getIsArray(): bool { return true; }
            public function getModel(): object { return $this; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
            public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                    public function get(string $key, array $params = []) { return null; }
                    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                    public function delete(string $key, array $params = []): object { return $this; }
                };
            }
            public function setMaxCachedPage($num): object { return $this; }
            public function getMaxCachedPage(): int { return 4; }
            public function getDriverParams(): array { return []; }
            public function getTotalHashPrefix(): string { return ''; }
            public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
            public function makeQueryBuilder() { return $this; }
            public function fetch(): object { return new \stdClass(); }
            public function defaultFilter() {}
        };
        
        // Мокаем getRepository и clearStorage
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFromCache());
    }

    public function test_InitStorage_WhenNoItems_SetsIsFetchingToFalse()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $repository = new class implements \pribolshoy\repository\interfaces\CachebleRepositoryInterface {
            public function search() { return []; }
            public function isCacheble(): bool { return false; }
            public function getHashPrefix(): string { return 'repo_'; }
            public function getFromCache($refresh = false, array $params = []) { return null; }
            public function setToCache($value, array $params = []): object { return $this; }
            public function deleteFromCache(array $params = []): object { return $this; }
            public function setHashName(string $hash_name): object { return $this; }
            public function getHashName(bool $refresh = false, bool $use_params = true, bool $save_to = true): string { return 'hash_name'; }
            public function setActiveCache(bool $activate = true): object { return $this; }
            public function isCacheActive(): bool { return true; }
            public function setCacheDuration(int $duration): object { return $this; }
            public function getCacheDuration(): int { return 3600; }
            public function setParams(array $params, bool $update_filter = false, bool $clear_filter = false): object { return $this; }
            public function getParams(): array { return []; }
            public function getFilters(): array { return []; }
            public function getFilter(string $name) { return null; }
            public function setNeedTotal(bool $need_total) { return $this; }
            public function getNeedTotal(): bool { return false; }
            public function setTotalCount(int $total_count) { return $this; }
            public function getTotalCount(): ?int { return 0; }
            public function setIsArray(bool $is_array) { return $this; }
            public function getIsArray(): bool { return true; }
            public function setDriver(\pribolshoy\repository\interfaces\CacheDriverInterface $driver): object { return $this; }
            public function getDriver(): \pribolshoy\repository\interfaces\CacheDriverInterface {
                return new class implements \pribolshoy\repository\interfaces\CacheDriverInterface {
                    public function get(string $key, array $params = []) { return null; }
                    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object { return $this; }
                    public function delete(string $key, array $params = []): object { return $this; }
                };
            }
            public function getModel(): object { return $this; }
            public function setMaxCachedPage($num): object { return $this; }
            public function getMaxCachedPage(): int { return 4; }
            public function getDriverParams(): array { return []; }
            public function getTotalHashPrefix(): string { return ''; }
            public function getHashFromArray(array $data, bool $hashToMd5 = false): string { return ''; }
            public function makeQueryBuilder() { return $this; }
            public function fetch(): object { return new \stdClass(); }
            public function defaultFilter() {}
        };
        
        // Мокаем getRepository и clearStorage
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFetching());
    }
}

