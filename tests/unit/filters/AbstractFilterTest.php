<?php

namespace pribolshoy\repository\tests\filters;

use pribolshoy\repository\filters\AbstractFilter;
use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteFilter extends AbstractFilter
{
    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        return [];
    }

    public function getByExp(array $attributes): array
    {
        return [];
    }

    public function getByMulti(array $attributes): array
    {
        return [];
    }

    public function getBy(array $attributes)
    {
        return null;
    }

    public function getById(int $id, array $attributes = [])
    {
        return null;
    }

    public function getByIds(array $ids, array $attributes = []): array
    {
        return [];
    }
}

class AbstractFilterTest extends CommonTestCase
{
    public function test_Constructor_WithService_SetsService()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        
        // Act
        $filter = new ConcreteFilter($service);
        
        // Assert
        $this->assertSame($service, $filter->getService());
    }

    public function test_GetService_ReturnsService()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        $filter = new ConcreteFilter($service);
        
        // Act
        $result = $filter->getService();
        
        // Assert
        $this->assertSame($service, $result);
    }

    public function test_FilterByAttributes_WithMatchingAttributes_ReturnsTrue()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemAttribute')
            ->willReturn('value1');
        
        $filter = new ConcreteFilter($service);
        $item = ['id' => 1, 'name' => 'test'];
        $attributes = ['name' => 'value1'];
        
        // Act
        $result = $filter->filterByAttributes($item, $attributes);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_FilterByAttributes_WithNonMatchingAttributes_ReturnsFalse()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemAttribute')
            ->willReturn('value1');
        
        $filter = new ConcreteFilter($service);
        $item = ['id' => 1, 'name' => 'test'];
        $attributes = ['name' => 'value2'];
        
        // Act
        $result = $filter->filterByAttributes($item, $attributes);
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_FilterByAttributes_WithArrayValue_MatchesCorrectly()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemAttribute')
            ->willReturn('value1');
        
        $filter = new ConcreteFilter($service);
        $item = ['id' => 1, 'name' => 'test'];
        $attributes = ['name' => ['value1', 'value2']];
        
        // Act
        $result = $filter->filterByAttributes($item, $attributes);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_FilterByAttributes_WithEmptyAttributes_ReturnsTrue()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        $filter = new ConcreteFilter($service);
        $item = ['id' => 1, 'name' => 'test'];
        $attributes = [];
        
        // Act
        $result = $filter->filterByAttributes($item, $attributes);
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_FilterByAttributes_WhenItemAttributeIsNull_ReturnsFalse()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemAttribute')
            ->willReturn(null);
        
        $filter = new ConcreteFilter($service);
        $item = ['id' => 1, 'name' => 'test'];
        $attributes = ['name' => 'value1'];
        
        // Act
        $result = $filter->filterByAttributes($item, $attributes);
        
        // Assert
        $this->assertFalse($result);
    }

    public function test_GetList_ReturnsArray()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        $filter = new ConcreteFilter($service);
        
        // Act
        $result = $filter->getList();
        
        // Assert
        $this->assertIsArray($result);
    }
}

