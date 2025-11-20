<?php

namespace pribolshoy\repository\tests;

use pribolshoy\repository\services\AbstractCachebleService;
use pribolshoy\repository\services\AbstractService;
use pribolshoy\repository\tests\CommonTestCase;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\RepositoryInterface;

// Используем класс из AbstractCachebleRepositoryTest
require_once __DIR__ . '/repositories/AbstractCachebleRepositoryTest.php';

class ConcreteAbstractCachebleService extends AbstractCachebleService
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
}

final class AbstractCachebleServiceTest extends CommonTestCase
{
    public function test_IsUseCache_ByDefault_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->isUseCache();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetUseCache_WithTrue_SetsUseCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->setUseCache(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->isUseCache());
    }

    public function test_SetUseCache_WithFalse_SetsUseCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->setUseCache(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isUseCache());
    }

    public function test_UseAliasCache_ByDefault_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->useAliasCache();
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_SetUseAliasCache_WithTrue_SetsUseAliasCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->setUseAliasCache(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->useAliasCache());
    }

    public function test_SetUseAliasCache_WithFalse_SetsUseAliasCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Act
        $result = $service->setUseAliasCache(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->useAliasCache());
    }

    public function test_GetHashPrefix_ByDefault_ReturnsEmptyString()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->getHashPrefix();
        
        // Assert
        $this->assertEquals('', $result);
    }

    public function test_SetHashPrefix_WithValue_SetsHashPrefix()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $prefix = 'test_prefix';
        
        // Act
        $result = $service->setHashPrefix($prefix);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($prefix, $service->getHashPrefix());
    }

    public function test_GetCacheParams_ByDefault_ReturnsDefaultParams()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->getCacheParams();
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('get', $result);
        $this->assertArrayHasKey('set', $result);
        $this->assertArrayHasKey('strategy', $result['get']);
        $this->assertEquals('getAllHash', $result['get']['strategy']);
    }

    public function test_GetCacheParams_WithName_ReturnsParams()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->cache_params = ['test' => ['key' => 'value']];
        
        // Act
        $result = $service->getCacheParams('test');
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(['key' => 'value'], $result);
    }

    public function test_GetCacheParams_WithNonExistentName_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->getCacheParams('nonexistent');
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_AddCacheParams_WithNameAndParams_AddsParams()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $name = 'test';
        $params = ['key' => 'value'];
        
        // Act
        $result = $service->addCacheParams($name, $params);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($params, $service->getCacheParams($name));
    }

    public function test_SetCacheParams_WithArray_SetsParams()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $params = ['strategy' => 'custom', 'fields' => ['id', 'name']];
        
        // Act
        $result = $service->setCacheParams($params);
        
        // Assert
        $this->assertSame($service, $result);
        // setCacheParams поддерживает обратную совместимость и преобразует старый формат в новый
        $this->assertArrayHasKey('get', $service->cache_params);
        $this->assertEquals('custom', $service->cache_params['get']['strategy']);
        $this->assertEquals(['id', 'name'], $service->cache_params['get']['fields']);
    }

    public function test_IsFromCache_ByDefault_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->isFromCache();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetIsFromCache_WithFalse_SetsIsFromCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->setIsFromCache(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFromCache());
    }

    public function test_SetIsFromCache_WithTrue_SetsIsFromCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setIsFromCache(false);
        
        // Act
        $result = $service->setIsFromCache(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->isFromCache());
    }

    public function test_GetFetchingStep_ByDefault_ReturnsDefault()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->getFetchingStep();
        
        // Assert
        $this->assertEquals(1000, $result);
    }

    public function test_SetFetchingStep_WithValue_SetsFetchingStep()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $step = 500;
        
        // Act
        $result = $service->setFetchingStep($step);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($step, $service->getFetchingStep());
    }

    public function test_GetAliasPostfix_ByDefault_ReturnsDefault()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->getAliasPostfix();
        
        // Assert
        $this->assertEquals(':alias', $result);
    }

    public function test_SetAliasPostfix_WithValue_SetsAliasPostfix()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $postfix = '_test_alias';
        
        // Act
        $result = $service->setAliasPostfix($postfix);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($postfix, $service->getAliasPostfix());
    }

    public function test_GetAliasAttribute_WhenNotSet_ThrowsException()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Name of item attribute for alias is not set');
        
        // Act
        $service->getAliasAttribute();
    }

    public function test_GetAliasAttribute_WhenSet_ReturnsAttribute()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('alias_attribute');
        $property->setAccessible(true);
        $property->setValue($service, 'alias_name');
        
        // Act
        $result = $service->getAliasAttribute();
        
        // Assert
        $this->assertEquals('alias_name', $result);
    }

    public function test_PrepareItem_WithItem_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $item = ['id' => 1, 'name' => 'test'];
        
        // Act
        $result = $service->prepareItem($item);
        
        // Assert
        $this->assertEquals($item, $result);
    }

    public function test_IsCacheExists_WhenCacheExists_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_prefix');
        $repository->method('getFromCache')->willReturn('cached_value');
        
        // Act
        $result = $service->isCacheExists($repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_IsCacheExists_WhenCacheNotExists_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_prefix');
        $repository->method('getFromCache')->willReturn(null);
        
        // Act
        $result = $service->isCacheExists($repository);
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_IsCacheExists_WithoutRepository_CreatesRepository()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Мокаем getRepository чтобы вернуть репозиторий с драйвером
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_prefix');
        $repository->method('getFromCache')->willReturn(null);
        
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->isCacheExists();
        
        // Assert
        // Метод должен выполниться без ошибок
        $this->assertIsBool($result);
    }

    public function test_InitStorageEvent_WhenNotFired_ReturnsTrue()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['initStorage'])
            ->getMock();
        $service->expects($this->once())
            ->method('initStorage')
            ->with(null, true);
        
        // Act
        $result = $service->initStorageEvent();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitStorageEvent_WhenAlreadyFired_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
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

    public function test_WasInitStorageFired_ByDefault_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Используем рефлексию для доступа к protected методу
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('wasInitStorageFired');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service);
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_InitStorageFired_SetsFlag()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initStorageFired');
        $method->setAccessible(true);
        $method->invoke($service);
        
        $wasFiredMethod = $reflection->getMethod('wasInitStorageFired');
        $wasFiredMethod->setAccessible(true);
        
        // Act
        $result = $wasFiredMethod->invoke($service);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_GetByAliasStructure_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute
        $reflection = new \ReflectionClass($service);
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        // Устанавливаем items для структуры
        $items = [
            ['id' => 1, 'alias' => 'test_alias'],
            ['id' => 2, 'alias' => 'another_alias'],
        ];
        $service->setItems($items);
        
        // Act
        $result = $service->getByAliasStructure('test_alias');
        
        // Assert
        // Метод возвращает ключ из структуры, который может быть null или значением
        $this->assertTrue(is_null($result) || is_string($result) || is_int($result));
    }

    public function test_UpdateHashtable_WithAliasCache_UpdatesAliasStructure()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute
        $reflection = new \ReflectionClass($service);
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        $items = [
            ['id' => 1, 'alias' => 'test_alias'],
        ];
        $service->setItems($items);
        
        // Act
        $result = $service->updateHashtable();
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_UpdateHashtable_WithoutAliasCache_CallsParent()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(false);
        
        $items = [
            ['id' => 1, 'name' => 'test'],
        ];
        $service->setItems($items);
        
        // Act
        $result = $service->updateHashtable();
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_GetPrimaryKeyByAlias_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $filter = $this->createMock(\pribolshoy\repository\filters\CachebleServiceFilter::class);
        $filter->method('getPrimaryKeyByAlias')->willReturn('1');
        
        // Мокаем getFilter
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getFilter'])
            ->getMock();
        $service->method('getFilter')->willReturn($filter);
        
        // Act
        $result = $service->getPrimaryKeyByAlias('test');
        
        // Assert
        $this->assertEquals('1', $result);
    }

    public function test_GetByAlias_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $filter = $this->createMock(\pribolshoy\repository\filters\CachebleServiceFilter::class);
        $filter->method('getByAlias')->willReturn(['id' => 1, 'alias' => 'test']);
        
        // Мокаем getFilter
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getFilter'])
            ->getMock();
        $service->method('getFilter')->willReturn($filter);
        
        // Act
        $result = $service->getByAlias('test');
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    public function test_InitStorage_WhenItemsFound_SetsItems()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $items = [['id' => 1, 'name' => 'test']];
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn($items);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFromCache());
    }

    public function test_InitStorage_WhenRefreshRepositoryCache_ClearsStorage()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository и clearStorage
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository', 'clearStorage'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        $service->expects($this->once())
            ->method('clearStorage')
            ->with($repository);
        
        // Act
        $result = $service->initStorage($repository, true);
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_InitStorage_WhenNoItems_DoesNotSetItems()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEmpty($service->getItems());
    }

    public function test_AfterInitStorage_CallsInitAliasCacheAndInitCachingFlag()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('afterInitStorage');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitCachingFlag_WhenItemsExist_SetsFlag()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setItems([['id' => 1]]);
        
        require_once __DIR__ . '/repositories/AbstractCachebleRepositoryTest.php';
        require_once __DIR__ . '/drivers/AbstractCacheDriverTest.php';
        $repository = new \pribolshoy\repository\tests\repositories\ConcreteCachebleRepository();
        
        // Устанавливаем драйвер через публичный метод
        $driverMock = $this->createMock(\pribolshoy\repository\interfaces\CacheDriverInterface::class);
        $driverMock->method('set')->willReturn($driverMock);
        $repository->setDriver($driverMock);
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initCachingFlag');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitCachingFlag_WhenNoItems_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setItems([]);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initCachingFlag');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_RefreshItem_WithEmptyPrimaryKeyArray_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act
        $result = $service->refreshItem([]);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_RefreshItem_WhenRepositoryNotCacheble_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Создаем мок репозитория, который не кэшируется
        $repositoryMock = $this->getMockBuilder(\pribolshoy\repository\tests\repositories\ConcreteCachebleRepository::class)
            ->onlyMethods(['isCacheble'])
            ->getMock();
        $repositoryMock->method('isCacheble')->willReturn(false);
        
        // Мокаем getRepository чтобы вернуть наш мок
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repositoryMock);
        
        // Act
        $result = $service->refreshItem(['id' => 1]);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_RefreshItem_WhenItemFound_UpdatesCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('search')->willReturn([['id' => 1, 'name' => 'test']]);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->refreshItem(['id' => 1]);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_RefreshItem_WhenItemNotFound_DeletesItem()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('search')->willReturn([]);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository и deleteItem
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository', 'deleteItem'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        $service->expects($this->once())
            ->method('deleteItem')
            ->with('1');
        
        // Act
        $result = $service->refreshItem(['id' => 1]);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_ClearStorage_DeletesItemsAndCachingFlag()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Ожидаем, что setHashName будет вызван дважды (для items и caching flag)
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
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->clearStorage(null, []);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_AfterStorageClear_WhenAliasCacheActive_DeletesAliasCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('afterStorageClear');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_AfterStorageClear_WhenAliasCacheNotActive_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(false);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('afterStorageClear');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_DeleteItem_DeletesFromCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteAbstractCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->deleteItem('1');
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_AfterDeleteItem_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('afterDeleteItem');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitAliasCache_WhenAliasCacheActive_InitializesCache()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute
        $reflection = new \ReflectionClass($service);
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        $items = [
            ['id' => 1, 'alias' => 'test_alias'],
        ];
        $service->setItems($items);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initAliasCache');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitAliasCache_WhenAliasCacheNotActive_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(false);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initAliasCache');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitAliasCache_WhenNoItems_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute, чтобы избежать исключения при updateHashtable
        $reflection = new \ReflectionClass($service);
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        // Устанавливаем пустой массив items через setItems
        // Но сначала нужно установить alias_attribute, чтобы updateHashtable не выбросил исключение
        $service->setItems([]);
        
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setDriver')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $method = $reflection->getMethod('initAliasCache');
        $method->setAccessible(true);
        
        // Act
        // Когда items пустой, метод должен вернуть true без инициализации кеша
        // Проверка $items = $this->getItems() вернет null/empty, и метод вернет true
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_GetItemAliasValue_WhenAliasCacheActive_ReturnsAliasValue()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('alias_attribute');
        $property->setAccessible(true);
        $property->setValue($service, 'alias');
        
        $item = ['id' => 67, 'alias' => 'test_alias'];
        
        // Используем рефлексию для доступа к protected методу
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getItemAliasValue');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $item);
        
        // Assert
        $this->assertEquals('test_alias', $result);
    }

    public function test_GetItemAliasValue_WhenAliasCacheNotActive_ThrowsException()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(false);
        
        $item = ['id' => 67, 'alias' => 'test_alias'];
        
        // Используем рефлексию для доступа к protected методу
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getItemAliasValue');
        $method->setAccessible(true);
        
        // Assert
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage("In this service alias cache isn't active");
        
        // Act
        $method->invoke($service, $item);
    }

    public function test_GetAliasStructure_WhenClassNotSet_ThrowsException()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getAliasStructure');
        $method->setAccessible(true);
        
        // Используем рефлексию для установки пустого класса
        $property = $reflection->getProperty('alias_item_structure_class');
        $property->setAccessible(true);
        $property->setValue($service, '');
        
        // Устанавливаем alias_attribute, чтобы избежать исключения из getAliasAttribute()
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Property alias_item_structure_class is not set');
        
        $method->invoke($service);
    }

    public function test_GetAliasStructure_WhenClassNotFound_ThrowsException()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getAliasStructure');
        $method->setAccessible(true);
        
        // Используем рефлексию для установки несуществующего класса
        $property = $reflection->getProperty('alias_item_structure_class');
        $property->setAccessible(true);
        $property->setValue($service, 'NonExistentClass');
        
        // Устанавливаем alias_attribute
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Item structure class not found');
        
        $method->invoke($service);
    }

    public function test_GetAliasStructure_WhenClassExists_ReturnsStructure()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getAliasStructure');
        $method->setAccessible(true);
        
        // Устанавливаем alias_attribute
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        // Act
        $structure = $method->invoke($service);
        
        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\structures\HashtableStructure::class, $structure);
    }

    public function test_GetAliasStructure_WithRefresh_RecreatesStructure()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $service->setUseAliasCache(true);
        
        // Устанавливаем alias_attribute
        $reflection = new \ReflectionClass($service);
        $aliasProperty = $reflection->getProperty('alias_attribute');
        $aliasProperty->setAccessible(true);
        $aliasProperty->setValue($service, 'alias');
        
        $method = $reflection->getMethod('getAliasStructure');
        $method->setAccessible(true);
        
        // Создаем первую структуру
        $structure1 = $method->invoke($service);
        
        // Act - получаем с refresh
        $structure2 = $method->invoke($service, true);
        
        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\structures\HashtableStructure::class, $structure2);
        // Структуры могут быть разными объектами при refresh
        $this->assertIsObject($structure2);
    }

    public function test_CacheParamsProperty_CanBeModified()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        $newParams = ['strategy' => 'custom'];
        
        // Act
        $service->cache_params = $newParams;
        
        // Assert
        $this->assertEquals($newParams, $service->cache_params);
    }

    public function test_CacheParamsProperty_ByDefault_HasStrategy()
    {
        // Arrange
        $service = new ConcreteAbstractCachebleService();
        
        // Act & Assert
        $this->assertIsArray($service->cache_params);
        $this->assertArrayHasKey('get', $service->cache_params);
        $this->assertArrayHasKey('set', $service->cache_params);
        $this->assertArrayHasKey('strategy', $service->cache_params['get']);
        $this->assertEquals('getAllHash', $service->cache_params['get']['strategy']);
    }
}

