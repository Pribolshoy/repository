<?php

namespace pribolshoy\repository\tests\filters;

use pribolshoy\repository\filters\EnormousServiceFilter;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\tests\CommonTestCase;

// Используем класс из CachebleServiceFilterTest
require_once __DIR__ . '/CachebleServiceFilterTest.php';

class EnormousServiceFilterTest extends CommonTestCase
{
    public function test_GetById_WithId_ReturnsItem()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getFromCache')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setParams')->willReturnSelf();
        $repository->method('search')->willReturn([['id' => 1, 'name' => 'test']]);
        
        $service->setRepository($repository);
        $service->setHashPrefix('test_prefix');
        
        $filter = new EnormousServiceFilter($service);
        
        // Act
        $result = $filter->getById(1);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByIds_WithIds_ReturnsItems()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $repository = $this->createMock(CachebleRepositoryInterface::class);
        $repository->method('setHashName')->willReturnSelf();
        $repository->method('getFromCache')->willReturn([]);
        $repository->method('isCacheble')->willReturn(true);
        $repository->method('setParams')->willReturnSelf();
        $repository->method('search')->willReturn([
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ]);
        
        $service->setRepository($repository);
        $service->setHashPrefix('test_prefix');
        
        $filter = new EnormousServiceFilter($service);
        
        // Act
        $result = $filter->getByIds([1, 2]);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByIds_WithEmptyArray_ReturnsEmptyArray()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $filter = new EnormousServiceFilter($service);
        
        // Act
        $result = $filter->getByIds([]);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
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
        
        $filter = new EnormousServiceFilter($service);
        
        // Act
        $result = $filter->getByAlias('test');
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetList_ThrowsException()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $filter = new EnormousServiceFilter($service);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method ' . EnormousServiceFilter::class . '::getList is not realized!');
        
        // Act
        $filter->getList();
    }

    public function test_GetByExp_ThrowsException()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $filter = new EnormousServiceFilter($service);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method ' . EnormousServiceFilter::class . '::getByExp is not realized!');
        
        // Act
        $filter->getByExp([]);
    }

    public function test_GetByMulti_ThrowsException()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $filter = new EnormousServiceFilter($service);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method ' . EnormousServiceFilter::class . '::getByMulti is not realized!');
        
        // Act
        $filter->getByMulti([]);
    }

    public function test_GetBy_ThrowsException()
    {
        // Arrange
        $service = new ConcreteCachebleServiceForFilter();
        $filter = new EnormousServiceFilter($service);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Method ' . EnormousServiceFilter::class . '::getBy is not realized!');
        
        // Act
        $filter->getBy([]);
    }
}

