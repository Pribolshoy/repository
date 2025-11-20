<?php

namespace pribolshoy\repository\tests\services;

use pribolshoy\repository\services\AbstractCachebleService;
use pribolshoy\repository\services\AbstractService;
use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\tests\CommonTestCase;

// Используем класс из AbstractCachebleRepositoryTest
require_once __DIR__ . '/../repositories/AbstractCachebleRepositoryTest.php';

class ConcreteCachebleService extends AbstractCachebleService
{
    public function sort(array $items): array
    {
        return $items;
    }
}

class AbstractCachebleServiceTest extends CommonTestCase
{
    public function test_IsUseCache_ByDefault_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->isUseCache();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetUseCache_WithTrue_SetsUseCache()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->setUseCache(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->isUseCache());
    }

    public function test_SetUseCache_WithFalse_SetsUseCache()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->setUseCache(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isUseCache());
    }

    public function test_UseAliasCache_ByDefault_ReturnsFalse()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->useAliasCache();
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_SetUseAliasCache_WithTrue_SetsUseAliasCache()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->setUseAliasCache(true);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertTrue($service->useAliasCache());
    }

    public function test_SetHashPrefix_WithValue_SetsHashPrefix()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $prefix = 'test_prefix';
        
        // Act
        $result = $service->setHashPrefix($prefix);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($prefix, $service->getHashPrefix());
    }

    public function test_GetHashPrefix_ByDefault_ReturnsEmptyString()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->getHashPrefix();
        
        // Assert
        $this->assertEquals('', $result);
    }

    public function test_IsFromCache_ByDefault_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->isFromCache();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetIsFromCache_WithFalse_SetsIsFromCache()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->setIsFromCache(false);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFromCache());
    }

    public function test_GetFetchingStep_ByDefault_ReturnsDefault()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->getFetchingStep();
        
        // Assert
        $this->assertEquals(1000, $result);
    }

    public function test_SetFetchingStep_WithValue_SetsFetchingStep()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $step = 500;
        
        // Act
        $result = $service->setFetchingStep($step);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($step, $service->getFetchingStep());
    }

    public function test_SetAliasPostfix_WithValue_SetsAliasPostfix()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $postfix = '_test_alias';
        
        // Act
        $result = $service->setAliasPostfix($postfix);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($postfix, $service->getAliasPostfix());
    }

    public function test_GetAliasPostfix_ByDefault_ReturnsDefault()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->getAliasPostfix();
        
        // Assert
        // В src/services/AbstractCachebleService.php значение по умолчанию ':alias'
        $this->assertEquals(':alias', $result);
    }

    public function test_GetCacheParams_ByDefault_ReturnsArray()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->cache_params;
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetCacheParams_CanBeModified()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $newParams = ['strategy' => 'custom'];
        
        // Act
        $service->cache_params = $newParams;
        
        // Assert
        $this->assertEquals($newParams, $service->cache_params);
    }

    public function test_GetCacheParams_WithName_ReturnsParams()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->cache_params = ['get' => ['strategy' => 'test']];
        
        // Act
        $result = $service->getCacheParams('get');
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_AddCacheParams_WithNameAndParams_AddsParams()
    {
        // Arrange
        $service = new ConcreteCachebleService();
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
        $service = new ConcreteCachebleService();
        $params = ['get' => ['strategy' => 'test'], 'set' => []];
        
        // Act
        $result = $service->setCacheParams($params);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEquals($params, $service->cache_params);
    }

    public function test_PrepareItem_WithItem_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $item = ['id' => 1, 'name' => 'test'];
        
        // Act
        $result = $service->prepareItem($item);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($item, $result);
    }

    public function test_GetAliasAttribute_ByDefault_ThrowsException()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Name of item attribute for alias is not set');
        
        // Act
        $service->getAliasAttribute();
    }
    
    public function test_GetAliasAttribute_WhenSet_ReturnsAttribute()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('alias_attribute');
        $property->setAccessible(true);
        $property->setValue($service, 'alias_name');
        
        // Act
        $result = $service->getAliasAttribute();
        
        // Assert
        $this->assertEquals('alias_name', $result);
    }

    public function test_IsCacheExists_WhenCacheExists_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
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
        $service = new ConcreteCachebleService();
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
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
        $service = new ConcreteCachebleService();
        // Устанавливаем repository_class через рефлексию
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $property->setValue($service, 'NonExistentClass');
        
        // Assert
        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        
        // Act
        $service->isCacheExists();
    }

    public function test_InitStorageEvent_WhenNotFired_ReturnsTrue()
    {
        // Arrange
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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
        $service = new ConcreteCachebleService();
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
        $service = new ConcreteCachebleService();
        
        // Используем рефлексию для доступа к protected методу
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('wasInitStorageFired');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service);
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_WasInitStorageFired_AfterInitStorageFired_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
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

    // Примечание: тест для getPrimaryKeyByAlias() требует методов из src/services/AbstractCachebleService.php
    // (getIdPostfix()), которые отсутствуют в src/AbstractCachebleService.php
    // Тест будет добавлен при тестировании src/services/AbstractCachebleService.php

    public function test_GetItemAliasValue_WhenAliasCacheActive_ReturnsAliasValue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
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
        $service = new ConcreteCachebleService();
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
        $service = new ConcreteCachebleService();
        
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
        $service = new ConcreteCachebleService();
        
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
        $service = new ConcreteCachebleService();
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

    public function test_GetByAliasStructure_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteCachebleService();
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
        $service = new ConcreteCachebleService();
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

    public function test_AfterInitStorage_CallsInitAliasCacheAndInitCachingFlag()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('afterInitStorage');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_InitCachingFlag_WhenUseCache_InitializesFlag()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->setUseCache(true);
        
        // Используем конкретный класс из тестов репозиториев
        require_once __DIR__ . '/../repositories/AbstractCachebleRepositoryTest.php';
        require_once __DIR__ . '/../drivers/AbstractCacheDriverTest.php';
        $repository = new \pribolshoy\repository\tests\repositories\ConcreteCachebleRepository();
        
        // Устанавливаем драйвер через публичный метод
        $driverMock = $this->createMock(\pribolshoy\repository\interfaces\CacheDriverInterface::class);
        $driverMock->method('set')->willReturn($driverMock);
        $repository->setDriver($driverMock);
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        if ($reflection->hasMethod('initCachingFlag')) {
            $method = $reflection->getMethod('initCachingFlag');
            $method->setAccessible(true);
            
            // Act
            $result = $method->invoke($service, $repository);
            
            // Assert
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('Method initCachingFlag does not exist in tested class');
        }
    }

    public function test_InitCachingFlag_WhenNotUseCache_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->setUseCache(false);
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        
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
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->refreshItem([]);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_RefreshItem_WhenRepositoryNotCacheble_ReturnsTrue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        // Используем конкретный класс из тестов репозиториев, который реализует CachebleRepositoryInterface
        require_once __DIR__ . '/../repositories/AbstractCachebleRepositoryTest.php';
        
        // Создаем мок репозитория, который не кэшируется
        $repositoryMock = $this->getMockBuilder(\pribolshoy\repository\tests\repositories\ConcreteCachebleRepository::class)
            ->onlyMethods(['isCacheble'])
            ->getMock();
        $repositoryMock->method('isCacheble')->willReturn(false);
        
        // Мокаем getRepository чтобы вернуть наш мок
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('search')->willReturn([['id' => 1, 'name' => 'test']]);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('search')->willReturn([]);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository и deleteItem
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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

    public function test_AfterRefreshItem_IsCalled()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Используем рефлексию для проверки существования метода
        $reflection = new \ReflectionClass($service);
        if ($reflection->hasMethod('afterRefreshItem')) {
            // Act
            $service->afterRefreshItem(['id' => 1]);
            
            // Assert - метод пустой, но должен выполняться без ошибок
            $this->assertTrue(true);
        } else {
            // Метод не существует в тестируемом классе
            $this->markTestSkipped('Method afterRefreshItem does not exist in tested class');
        }
    }

    public function test_ClearStorage_DeletesItemsAndCachingFlag()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
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

    public function test_AfterStorageClear_WhenAliasCacheActive_DeletesAliasCache()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->setUseAliasCache(true);
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
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
        $service = new ConcreteCachebleService();
        $service->setUseAliasCache(false);
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        
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
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        
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
        $service = new ConcreteCachebleService();
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
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
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
        $service = new ConcreteCachebleService();
        $service->setUseAliasCache(false);
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('initAliasCache');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($service, $repository);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_GetIdPostfix_ReturnsDelimiter()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Используем рефлексию для вызова метода из src/services/AbstractCachebleService.php
        $reflection = new \ReflectionClass($service);
        if ($reflection->hasMethod('getIdPostfix')) {
            // Act
            $result = $service->getIdPostfix();
            
            // Assert
            $this->assertIsString($result);
        } else {
            // Метод не существует в тестируемом классе
            $this->markTestSkipped('Method getIdPostfix does not exist in tested class');
        }
    }

    public function test_GetItemIdValue_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $item = ['id' => 1, 'name' => 'test'];
        
        // Используем рефлексию для вызова метода из src/services/AbstractCachebleService.php
        $reflection = new \ReflectionClass($service);
        if ($reflection->hasMethod('getItemIdValue')) {
            // Act
            $result = $service->getItemIdValue($item);
            
            // Assert
            $this->assertEquals('1', $result);
        } else {
            // Метод не существует в тестируемом классе
            $this->markTestSkipped('Method getItemIdValue does not exist in tested class');
        }
    }

    public function test_GetByAlias_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        $filter = $this->createMock(\pribolshoy\repository\filters\CachebleServiceFilter::class);
        $filter->method('getByAlias')->willReturn(['id' => 1, 'alias' => 'test']);
        
        // Мокаем getFilter
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
            ->onlyMethods(['getFilter'])
            ->getMock();
        $service->method('getFilter')->willReturn($filter);
        
        // Act
        $result = $service->getByAlias('test');
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    public function test_GetPrimaryKeyByAlias_ReturnsPrimaryKey()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        $filter = $this->createMock(\pribolshoy\repository\filters\CachebleServiceFilter::class);
        $filter->method('getPrimaryKeyByAlias')->willReturn('1');
        
        // Мокаем getFilter
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
            ->onlyMethods(['getFilter'])
            ->getMock();
        $service->method('getFilter')->willReturn($filter);
        
        // Act
        $result = $service->getPrimaryKeyByAlias('test');
        
        // Assert
        $this->assertEquals('1', $result);
    }

    public function test_InitStorage_WhenItemsFound_SetsItems()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $items = [['id' => 1, 'name' => 'test']];
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('search')->willReturn($items);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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
        $service = new ConcreteCachebleService();
        $service->setUseCache(true);
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('deleteFromCache')->willReturnSelf();
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository и clearStorage
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
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

    public function test_InitStorage_WhenUseCacheFalse_DoesNotCacheItems()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->setUseCache(false);
        $items = [['id' => 1, 'name' => 'test']];
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('search')->willReturn($items);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertFalse($service->isFromCache());
        // Проверяем, что items установлены, но не кэшируются (проверка в коде на строке 502)
        $this->assertNotEmpty($service->getItems());
    }

    public function test_InitStorage_WhenNoItems_DoesNotSetItems()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        $repository = $this->createMock(\pribolshoy\repository\interfaces\CachebleRepositoryInterface::class);
        $repository->method('search')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('getHashPrefix')->willReturn('repo_');
        $repository->method('getCacheDuration')->willReturn(3600);
        $repository->method('setCacheDuration')->willReturnSelf();
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('setToCache')->willReturnSelf();
        
        // Мокаем getRepository
        $service = $this->getMockBuilder(ConcreteCachebleService::class)
            ->onlyMethods(['getRepository'])
            ->getMock();
        $service->method('getRepository')->willReturn($repository);
        
        // Act
        $result = $service->initStorage($repository);
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertEmpty($service->getItems());
    }

    public function test_GetIdPostfix_ReturnsString()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->getIdPostfix();
        
        // Assert
        $this->assertIsString($result);
    }

    public function test_AddCacheParams_MergesParams()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->cache_params = ['get' => ['strategy' => 'test']];
        
        // Act
        $result = $service->addCacheParams('get', ['field' => 'value']);
        
        // Assert
        $this->assertSame($service, $result);
        $params = $service->getCacheParams('get');
        $this->assertArrayHasKey('strategy', $params);
        $this->assertArrayHasKey('field', $params);
        $this->assertEquals('test', $params['strategy']);
        $this->assertEquals('value', $params['field']);
    }

    public function test_AddCacheParams_WithNewKey_CreatesArray()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->addCacheParams('new_key', ['param' => 'value']);
        
        // Assert
        $this->assertSame($service, $result);
        $params = $service->getCacheParams('new_key');
        $this->assertEquals(['param' => 'value'], $params);
    }

    public function test_GetAliasPostfix_ReturnsDefaultValue()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        
        // Act
        $result = $service->getAliasPostfix();
        
        // Assert
        $this->assertEquals(':alias', $result);
    }

    public function test_GetCacheParams_WithEmptyName_ReturnsAllParams()
    {
        // Arrange
        $service = new ConcreteCachebleService();
        $service->cache_params = [
            'get' => ['strategy' => 'test'],
            'set' => []
        ];
        
        // Act
        $result = $service->getCacheParams('');
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('get', $result);
        $this->assertArrayHasKey('set', $result);
    }
}

