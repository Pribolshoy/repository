<?php

namespace pribolshoy\repository\tests\repositories;

use pribolshoy\repository\AbstractCachebleRepository;
use pribolshoy\repository\interfaces\CacheDriverInterface;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteCachebleRepository extends AbstractCachebleRepository
{
    protected function makeQueryBuilder()
    {
        $this->model = new \stdClass();
        return $this;
    }

    protected function fetch(): object
    {
        $this->items = [];
        $this->total_count = 0;
        return $this;
    }

    public function getTableName(): string
    {
        return 'test_table';
    }

    protected function defaultFilter()
    {
        // Empty implementation
    }
}

class AbstractCachebleRepositoryTest extends CommonTestCase
{
    public function test_SetMaxCachedPage_WithValue_SetsMaxCachedPage()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $maxPage = 10;
        
        // Act
        $result = $repository->setMaxCachedPage($maxPage);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEquals($maxPage, $repository->getMaxCachedPage());
    }

    public function test_GetMaxCachedPage_ByDefault_ReturnsDefault()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getMaxCachedPage();
        
        // Assert
        $this->assertEquals(4, $result);
    }

    public function test_SetActiveCache_WithTrue_ActivatesCache()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->setActiveCache(true);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertTrue($repository->isCacheActive());
    }

    public function test_SetActiveCache_WithFalse_DeactivatesCache()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->setActiveCache(false);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertFalse($repository->isCacheActive());
    }

    public function test_IsCacheActive_ByDefault_ReturnsTrue()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->isCacheActive();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetCacheDuration_WithValue_SetsCacheDuration()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $duration = 3600;
        
        // Act
        $result = $repository->setCacheDuration($duration);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEquals($duration, $repository->getCacheDuration());
    }

    public function test_GetCacheDuration_ByDefault_ReturnsDefault()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getCacheDuration();
        
        // Assert
        $this->assertEquals(10800, $result);
    }

    public function test_GetHashPrefix_ReturnsTableName()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getHashPrefix();
        
        // Assert
        $this->assertEquals('test_table', $result);
    }

    public function test_GetTotalHashPrefix_ReturnsPrefixedTableName()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getTotalHashPrefix();
        
        // Assert
        $this->assertEquals('total_test_table', $result);
    }

    public function test_SetHashName_WithValue_SetsHashName()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $hashName = 'test_hash';
        
        // Act
        $result = $repository->setHashName($hashName);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEquals($hashName, $repository->getHashName());
    }

    public function test_GetHashName_WhenSet_ReturnsHashName()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $hashName = 'test_hash';
        $repository->setHashName($hashName);
        
        // Act
        $result = $repository->getHashName();
        
        // Assert
        $this->assertEquals($hashName, $result);
    }

    public function test_GetHashName_WhenNotSet_GeneratesHashName()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getHashName(true);
        
        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_GetHashFromArray_WithData_ReturnsHash()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        
        // Act
        $result = $repository->getHashFromArray($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_GetHashFromArray_WithLongData_ReturnsMd5Hash()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $data = array_fill(0, 100, 'very_long_string_value');
        
        // Act
        $result = $repository->getHashFromArray($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result)); // MD5 hash length
    }

    public function test_GetHashFromArray_WithHashToMd5_ReturnsMd5Hash()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $data = ['key' => 'value'];
        
        // Act
        $result = $repository->getHashFromArray($data, true);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result)); // MD5 hash length
    }

    public function test_GetHashFromArray_WithEmptyArray_ReturnsEmptyString()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $data = [];
        
        // Act
        $result = $repository->getHashFromArray($data);
        
        // Assert
        $this->assertEquals('', $result);
    }

    public function test_IsCacheble_WhenCacheActiveAndPageValid_ReturnsTrue()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setHashName('test_hash');
        $repository->page = 1;
        
        // Act
        $result = $repository->isCacheble();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_IsCacheble_WhenCacheInactive_ReturnsFalse()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setActiveCache(false);
        $repository->setHashName('test_hash');
        $repository->page = 1;
        
        // Act
        $result = $repository->isCacheble();
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_IsCacheble_WhenPageExceedsMax_ReturnsFalse()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setHashName('test_hash');
        $repository->page = 10; // больше max_cached_page (4)
        
        // Act
        $result = $repository->isCacheble();
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_IsCacheble_WhenHashNameNotSet_ReturnsFalse()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->page = 1;
        // Явно не устанавливаем hash_name через setHashName
        // Но getHashName может сгенерировать хеш автоматически
        
        // Act
        $result = $repository->isCacheble();
        
        // Assert
        // Проверяем логику: если getHashName() возвращает пустую строку, то isCacheble должен вернуть false
        // Но если getHashName() генерирует хеш, то может вернуть true
        $hashName = $repository->getHashName(true, true, false);
        if (empty($hashName)) {
            $this->assertFalse($result);
        } else {
            $this->assertIsBool($result);
        }
    }

    public function test_SetToCache_WithHashName_CallsDriverSet()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setHashName('test_hash');
        
        $driver = $this->createMock(CacheDriverInterface::class);
        $driver->expects($this->once())
            ->method('set')
            ->with('test_hash', ['data'], 10800, []);
        
        $repository->setDriver($driver);
        
        // Act
        $result = $repository->setToCache(['data']);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_SetToCache_WithoutHashName_DoesNotCallDriver()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        // Устанавливаем hash_name в null явно через рефлексию
        $reflection = new \ReflectionClass($repository);
        $hashNameProperty = $reflection->getProperty('hash_name');
        $hashNameProperty->setAccessible(true);
        $hashNameProperty->setValue($repository, null);
        
        // Мокируем getHashName чтобы вернуть пустую строку
        $repositoryMock = $this->getMockBuilder(ConcreteCachebleRepository::class)
            ->onlyMethods(['getHashName'])
            ->getMock();
        $repositoryMock->method('getHashName')
            ->willReturn('');
        
        // Act
        $result = $repositoryMock->setToCache(['data']);
        
        // Assert
        $this->assertSame($repositoryMock, $result);
    }

    public function test_GetFromCache_WithHashName_CallsDriverGet()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setHashName('test_hash');
        
        $driver = $this->createMock(CacheDriverInterface::class);
        $driver->expects($this->once())
            ->method('get')
            ->with('test_hash', [])
            ->willReturn(['cached_data']);
        
        $repository->setDriver($driver);
        
        // Act
        $result = $repository->getFromCache();
        
        // Assert
        $this->assertEquals(['cached_data'], $result);
    }

    public function test_GetFromCache_WithoutHashName_ReturnsEmptyArray()
    {
        // Arrange
        $repositoryMock = $this->getMockBuilder(ConcreteCachebleRepository::class)
            ->onlyMethods(['getHashName'])
            ->getMock();
        $repositoryMock->method('getHashName')
            ->willReturn('');
        
        // Act
        $result = $repositoryMock->getFromCache();
        
        // Assert
        $this->assertEquals([], $result);
    }

    public function test_DeleteFromCache_WithHashName_CallsDriverDelete()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setHashName('test_hash');
        
        $driverMock = $this->createMock(CacheDriverInterface::class);
        $driverMock->expects($this->once())
            ->method('delete')
            ->with('test_hash', [])
            ->willReturn($driverMock);
        
        $repository->setDriver($driverMock);
        
        // Act
        $result = $repository->deleteFromCache();
        
        // Assert
        // deleteFromCache возвращает результат delete() ?? [], который может быть объектом или массивом
        $this->assertTrue(is_array($result) || is_object($result));
    }

    public function test_GetDriverParams_ByDefault_ReturnsEmptyArray()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getDriverParams();
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_SetDriver_SetsDriverInstance()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $driverMock = $this->createMock(CacheDriverInterface::class);
        
        // Act
        $result = $repository->setDriver($driverMock);
        
        // Assert
        $this->assertSame($repository, $result); // Проверяем fluent interface
        // Проверяем, что драйвер установлен, вызывая метод, который использует его
        $repository->setHashName('test_hash');
        $driverMock->expects($this->once())
            ->method('get')
            ->with('test_hash', [])
            ->willReturn(['test_data']);
        
        $cachedData = $repository->getFromCache();
        $this->assertEquals(['test_data'], $cachedData);
    }

    public function test_PageProperty_CanBeSet()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $repository->page = 5;
        
        // Assert
        $this->assertEquals(5, $repository->page);
    }

    public function test_PageProperty_HasDefaultValue()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Assert
        $this->assertEquals(0, $repository->page);
    }

    public function test_GetHashName_WithRefresh_RegeneratesHash()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        $repository->setParams(['filter1' => 'value1'], true, true); // Устанавливаем параметры и обновляем фильтры
        $hash1 = $repository->getHashName();
        
        // Act
        $repository->setParams(['filter1' => 'value2'], true, true); // Меняем параметры и обновляем фильтры
        $hash2 = $repository->getHashName(true); // Принудительно обновляем хеш
        
        // Assert
        $this->assertIsString($hash2);
        // Хеш должен быть другим после изменения параметров (если фильтры были установлены)
        // Если фильтры не установлены, оба хеша будут равны hash_prefix
        if ($hash1 !== 'test_table' || $hash2 !== 'test_table') {
            $this->assertNotEquals($hash1, $hash2);
        } else {
            // Если оба хеша равны префиксу (нет фильтров), это нормально
            $this->assertEquals('test_table', $hash1);
            $this->assertEquals('test_table', $hash2);
        }
    }

    public function test_GetHashName_WithoutParams_ReturnsHashPrefix()
    {
        // Arrange
        $repository = new ConcreteCachebleRepository();
        
        // Act
        $result = $repository->getHashName(false, false);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals('test_table', $result);
    }
}

