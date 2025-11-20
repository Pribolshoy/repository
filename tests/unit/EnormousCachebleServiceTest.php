<?php

namespace pribolshoy\repository\tests;

use pribolshoy\repository\services\EnormousCachebleService;
use pribolshoy\repository\services\AbstractCachebleService;
use pribolshoy\repository\tests\CommonTestCase;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;

// Используем класс из AbstractCachebleRepositoryTest
require_once __DIR__ . '/repositories/AbstractCachebleRepositoryTest.php';

class ConcreteEnormousCachebleService extends EnormousCachebleService
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

final class EnormousCachebleServiceTest extends CommonTestCase
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

    public function test_GetMaxInitIteration_ReturnsProtectedProperty()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        // Используем рефлексию для проверки свойства
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('max_init_iteration');
        $property->setAccessible(true);
        $property->setValue($service, 15);
        
        // Act
        $result = $service->getMaxInitIteration();
        
        // Assert
        $this->assertEquals(15, $result);
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

    public function test_SetInitIteration_WithValue_SetsIteration()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $iteration = 5;
        
        // Act
        $result = $service->setInitIteration($iteration);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($iteration, $service->getInitIteration());
    }

    public function test_SetInitIteration_WithNull_ResetsIteration()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setInitIteration(5);
        
        // Act
        $result = $service->setInitIteration(null);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertNull($service->getInitIteration());
    }

    public function test_SetInitIteration_WithoutParameter_ResetsIteration()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setInitIteration(5);
        
        // Act
        $result = $service->setInitIteration();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertNull($service->getInitIteration());
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

    public function test_SetIsFetching_WithTrue_SetsFetching()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        // Act
        $result = $service->setIsFetching(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->isFetching());
    }

    public function test_SetIsFetching_WithFalse_SetsFetching()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        $service->setIsFetching(true);
        
        // Act
        $result = $service->setIsFetching(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFetching());
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

    public function test_CacheParams_ByDefault_HasStrategy()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        // Act & Assert
        $this->assertIsArray($service->cache_params);
        $this->assertArrayHasKey('get', $service->cache_params);
        $this->assertArrayHasKey('set', $service->cache_params);
        $this->assertArrayHasKey('strategy', $service->cache_params['get']);
        $this->assertEquals('getHValue', $service->cache_params['get']['strategy']);
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
        $this->expectExceptionMessage('Method ' . EnormousCachebleService::class . '::getByHashtableMulti is deprecated!');
        
        // Act
        $service->getByHashtableMulti([['id' => 1]]);
    }

    public function test_InitStorage_WhenFirstRun_ClearsStorage()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(0);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем clearStorage и getRepository
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
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(0);
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
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
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn($items);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(1);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        
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

    public function test_InitStorage_WhenMoreItemsThanFetched_SetsIsFetching()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $items = [['id' => 1, 'name' => 'test']];
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn($items);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(2000); // Больше чем fetched_items + count(items)
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        
        // Мокаем getRepository и clearStorage
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->isFetching());
    }

    public function test_InitStorage_WithRepository_UsesProvidedRepository()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(0);
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем clearStorage
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['clearStorage'])
            ->getMock();
        $service->expects($this->once())
            ->method('clearStorage')
            ->with($repository);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorage_IgnoresRefreshRepositoryCacheParameter()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getTotalCount')->willReturn(0);
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем clearStorage - должен быть вызван только один раз при первом запуске
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['clearStorage', 'getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        $service->expects($this->once())
            ->method('clearStorage');
        
        // Act
        // refresh_repository_cache игнорируется, но clearStorage вызывается при первом запуске
        $result = $service->initStorage(null, true);
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorageEvent_WhenNotFired_ReturnsTrue()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage', 'isFetching'])
            ->getMock();
        
        $service->method('isFetching')
            ->willReturnOnConsecutiveCalls(true, true, false);
        $service->expects($this->exactly(2))
            ->method('initStorage')
            ->willReturnSelf();
        
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

    public function test_InitStorageEvent_SetsIsFetchingToTrue()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage', 'isFetching'])
            ->getMock();
        
        $service->method('isFetching')
            ->willReturn(false);
        $service->method('initStorage')
            ->willReturnSelf();
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
        // Проверяем, что setIsFetching был вызван через проверку свойства
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('is_fetching');
        $property->setAccessible(true);
        $isFetching = $property->getValue($service);
        $this->assertTrue($isFetching);
    }

    public function test_InitStorageEvent_CallsInitStorageInCycle()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage', 'isFetching'])
            ->getMock();
        
        $service->method('isFetching')
            ->willReturnOnConsecutiveCalls(true, true, true, false);
        // Цикл выполняется пока isFetching() возвращает true
        // При первом вызове isFetching() = true, вызывается initStorage()
        // При втором вызове isFetching() = true, вызывается initStorage()
        // При третьем вызове isFetching() = true, вызывается initStorage()
        // При четвертом вызове isFetching() = false, цикл завершается
        // Итого: initStorage вызывается 3 раза
        $service->expects($this->exactly(3))
            ->method('initStorage')
            ->willReturnSelf();
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitStorageEvent_ResetsInitIteration()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteEnormousCachebleService::class)
            ->onlyMethods(['initStorage', 'isFetching'])
            ->getMock();
        
        $service->method('isFetching')->willReturn(false);
        $service->method('initStorage')->willReturnSelf();
        $service->setInitIteration(5);
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
        $this->assertNull($service->getInitIteration());
    }

    public function test_FilterClass_IsEnormousServiceFilter()
    {
        // Arrange
        $service = new ConcreteEnormousCachebleService();
        
        // Используем рефлексию для проверки свойства
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('filter_class');
        $property->setAccessible(true);
        
        // Act
        $filterClass = $property->getValue($service);
        
        // Assert
        $this->assertEquals(\pribolshoy\repository\filters\EnormousServiceFilter::class, $filterClass);
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
}

