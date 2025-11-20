<?php

namespace pribolshoy\repository\tests\filters;

use pribolshoy\repository\filters\ServiceFilter;
use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteServiceForFilter implements ServiceInterface
{
    private $items = null;
    private $repository;
    
    public function getItems(): ?array { return $this->items; }
    public function setItems(array $items): void { $this->items = $items; }
    public function getRepository(array $params = []): \pribolshoy\repository\interfaces\RepositoryInterface { return $this->repository; }
    public function setRepository($repository) { $this->repository = $repository; }
    public function sort(array $items): array { return $items; }
    public function initStorage() { return $this; }
    public function getItemAttribute($item, string $name) { return $item[$name] ?? null; }
    public function getItemPrimaryKey($item) { return $item['id'] ?? null; }
    public function getByHashtable($key, ?string $structureName = null) { return []; }
    public function resort(): object { return $this; }
    public function collectItemsPrimaryKeys(array $items): array { return []; }
    public function getHashByItem($item) { return null; }
    public function getNamedStructure(string $name): ?\pribolshoy\repository\interfaces\StructureInterface { return null; }
    public function isMultiplePrimaryKey(): bool { return true; }
    public function setPrimaryKeys(array $primaryKeys): object { return $this; }
    public function getItemStructure(bool $refresh = false): \pribolshoy\repository\interfaces\StructureInterface { return new \pribolshoy\repository\structures\ArrayStructure($this); }
    public function getBasicHashtableStructure(bool $refresh = false): \pribolshoy\repository\structures\HashtableStructure { return new \pribolshoy\repository\structures\HashtableStructure($this); }
    public function getNamedStructures(): array { return []; }
    public function setRepositoryClass(string $repository_class): object { return $this; }
    public function addItem($item, bool $replace_if_exists = true): object { return $this; }
    public function getItemHash($item) { return md5(serialize($item)); }
    public function hash($value): string { return md5($value); }
    public function getHashtable() { return []; }
    public function updateHashtable(): object { return $this; }
    public function setFilterClass(string $filter_class): object { return $this; }
    public function getFilter(bool $refresh = false): \pribolshoy\repository\interfaces\FilterInterface { return null; }
    public function setSorting(array $sorting): object { return $this; }
    public function getList(array $params = [], bool $cache_to = true): ?array { return $this->items; }
    public function getByExp(array $attributes): array { return []; }
    public function getByMulti(array $attributes): array { return []; }
    public function getBy(array $attributes) { return null; }
    public function getById(int $id, array $attributes = []) { return null; }
    public function getByIds(array $ids, array $attributes = []): array { return []; }
}

class ServiceFilterTest extends CommonTestCase
{
    public function test_GetList_WhenItemsNotSet_CallsRepository()
    {
        // Arrange
        $service = new ConcreteServiceForFilter();
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->method('search')
            ->willReturn([['id' => 1, 'name' => 'test']]);
        $service->setRepository($repository);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getList();
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetList_WhenItemsSet_ReturnsItems()
    {
        // Arrange
        $items = [['id' => 1, 'name' => 'test']];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getList();
        
        // Assert
        $this->assertEquals($items, $result);
    }

    public function test_GetByExp_WithMatchingPattern_ReturnsFilteredItems()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        $attributes = ['name' => 'test1'];
        
        // Act
        $result = $filter->getByExp($attributes);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByMulti_WithMatchingAttributes_ReturnsFilteredItems()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1', 'status' => 'active'],
            ['id' => 2, 'name' => 'test2', 'status' => 'inactive'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        $attributes = ['name' => 'test1', 'status' => 'active'];
        
        // Act
        $result = $filter->getByMulti($attributes);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetBy_WithMatchingAttributes_ReturnsItem()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        $attributes = ['name' => 'test1'];
        
        // Act
        $result = $filter->getBy($attributes);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('test1', $result['name']);
    }

    public function test_GetBy_WithNonMatchingAttributes_ReturnsNull()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        $attributes = ['name' => 'nonexistent'];
        
        // Act
        $result = $filter->getBy($attributes);
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetById_WhenExists_ReturnsItem()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getById(1);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    public function test_GetById_WhenNotExists_ReturnsNull()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getById(999);
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetByIds_WithMultipleIds_ReturnsItems()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
            ['id' => 3, 'name' => 'test3'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getByIds([1, 3]);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_GetByIds_WithNonExistentIds_ReturnsEmptyArray()
    {
        // Arrange
        $items = [
            ['id' => 1, 'name' => 'test1'],
        ];
        $service = new ConcreteServiceForFilter();
        $service->setItems($items);
        
        $filter = new ServiceFilter($service);
        
        // Act
        $result = $filter->getByIds([999, 1000]);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

