<?php

namespace pribolshoy\repository\tests;

use pribolshoy\repository\services\PaginatedCachebleService;
use pribolshoy\repository\services\AbstractCachebleService;
use pribolshoy\repository\tests\CommonTestCase;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;

// Используем класс из AbstractCachebleRepositoryTest
require_once __DIR__ . '/repositories/AbstractCachebleRepositoryTest.php';

class ConcretePaginatedCachebleService extends PaginatedCachebleService
{
    public function sort(array $items): array
    {
        return $items;
    }
    
    public function getItemAttribute($item, string $name)
    {
        if (is_array($item)) {
            return $item[$name] ?? null;
        } elseif (is_object($item) && isset($item->$name)) {
            return $item->$name;
        }
        return null;
    }
    
    public function getItemPrimaryKey($item)
    {
        return $this->getItemAttribute($item, 'id');
    }
    
    public function getRepository(array $params = []): RepositoryInterface
    {
        return new \pribolshoy\repository\tests\repositories\ConcreteCachebleRepository();
    }
    
    public function getTableName(): string
    {
        return 'test_table';
    }
    
    public function defaultFilter()
    {
        // Empty implementation
    }
    
    public function fetch(): object
    {
        return new \stdClass();
    }
    
    public function prepareItem($item)
    {
        return $item;
    }
    
    public function getAliasAttribute(): string
    {
        return 'name';
    }
}

final class PaginatedCachebleServiceTest extends CommonTestCase
{
    public function test_GetHashPrefix_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        
        // Act
        $result = $service->getHashPrefix();
        
        // Assert
        $this->assertEquals('list:', $result);
    }

    public function test_PaginationPrefix_IsPublicProperty()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        
        // Act
        $result = $service->pagination_prefix;
        
        // Assert
        $this->assertEquals('pagination:', $result);
    }

    public function test_PaginationPrefix_CanBeModified()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $newPrefix = 'custom_pagination_';
        
        // Act
        $service->pagination_prefix = $newPrefix;
        
        // Assert
        $this->assertEquals($newPrefix, $service->pagination_prefix);
    }

    public function test_CacheParams_ByDefault_HasStrategy()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        
        // Act & Assert
        $this->assertIsArray($service->cache_params);
        $this->assertArrayHasKey('get', $service->cache_params);
        $this->assertArrayHasKey('set', $service->cache_params);
        $this->assertArrayHasKey('strategy', $service->cache_params['get']);
        $this->assertEquals('getValue', $service->cache_params['get']['strategy']);
    }

    public function test_CacheParams_CanBeModified()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $newParams = ['strategy' => 'custom'];
        
        // Act
        $service->cache_params = $newParams;
        
        // Assert
        $this->assertEquals($newParams, $service->cache_params);
    }

    public function test_FilterClass_IsPaginatedServiceFilter()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        
        // Используем рефлексию для проверки свойства
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('filter_class');
        $property->setAccessible(true);
        
        // Act
        $filterClass = $property->getValue($service);
        
        // Assert
        $this->assertEquals(\pribolshoy\repository\filters\PaginatedServiceFilter::class, $filterClass);
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

    public function test_InitStorage_WithRepository_ReturnsSelf()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(RepositoryInterface::class);
        
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

    public function test_InitStorage_WithRefreshRepositoryCache_ReturnsSelf()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(RepositoryInterface::class);
        
        // Act
        $result = $service->initStorage($repository, true);
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorage_IgnoresParameters()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(RepositoryInterface::class);
        
        // Act
        // Метод должен просто вернуть $this, не выполняя никаких операций
        $result = $service->initStorage($repository, true);
        
        // Assert
        $this->assertSame($service, $result);
        // Проверяем, что items не были установлены (метод не должен их устанавливать)
        $this->assertNull($service->getItems());
    }

    public function test_ClearStorage_WithRepository_DeletesEntitiesAndPaginationCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
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
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcretePaginatedCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->clearStorage(null, []);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_DeletesEntitiesCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Проверяем, что setHashName вызывается с правильными параметрами
        $repository->expects($this->exactly(2))
            ->method('setHashName')
            ->withConsecutive(
                ['list:test_prefix'],
                ['pagination:test_prefix']
            )
            ->willReturnSelf();
        
        // Act
        $result = $service->clearStorage($repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_DeletesPaginationCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Проверяем, что setHashName вызывается с правильными параметрами для pagination
        $repository->expects($this->exactly(2))
            ->method('setHashName')
            ->withConsecutive(
                ['list:test_prefix'],
                ['pagination:test_prefix']
            )
            ->willReturnSelf();
        
        // Act
        $result = $service->clearStorage($repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_WithParams_PassesParamsToRepository()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        $params = ['param1' => 'value1'];
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcretePaginatedCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->expects($this->once())
            ->method('getRepository')
            ->with($params)
            ->willReturn($repository);
        
        // Act
        $result = $service->clearStorage(null, $params);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_ReturnsBool()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('test_prefix');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Act
        $result = $service->clearStorage($repository);
        
        // Assert
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function test_InitStorage_DoesNotModifyItems()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $service->setItems([['id' => 1, 'name' => 'test']]);
        $repository = $this->createMock(RepositoryInterface::class);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        // Метод не должен изменять items (он просто возвращает $this)
        $items = $service->getItems();
        $this->assertNotNull($items);
    }

    public function test_InitStorage_DoesNotCallRepositorySearch()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $repository = $this->createMock(RepositoryInterface::class);
        
        // Ожидаем, что search() не будет вызван
        $repository->expects($this->never())->method('search');
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorage_DoesNotSetIsFromCache()
    {
        // Arrange
        $service = new ConcretePaginatedCachebleService();
        $service->setIsFromCache(true);
        $repository = $this->createMock(RepositoryInterface::class);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        // isFromCache должен остаться true (метод не изменяет его)
        $this->assertTrue($service->isFromCache());
    }
}

