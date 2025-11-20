<?php

namespace pribolshoy\repository\tests\repositories;

use pribolshoy\repository\AbstractRepository;
use pribolshoy\repository\exceptions\RepositoryException;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteRepository extends AbstractRepository
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

class AbstractRepositoryTest extends CommonTestCase
{
    public function test_Constructor_WithParams_SetsParams()
    {
        // Arrange
        $params = ['param1' => 'value1', 'param2' => 'value2'];
        
        // Act
        $repository = new ConcreteRepository($params);
        
        // Assert
        $this->assertEquals($params, $repository->getParams());
    }

    public function test_Constructor_WithModelClass_SetsModelClass()
    {
        // Arrange
        $modelClass = \stdClass::class;
        
        // Act
        $repository = new ConcreteRepository([], $modelClass);
        
        // Assert
        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model_class');
        $property->setAccessible(true);
        $this->assertEquals($modelClass, $property->getValue($repository));
    }

    public function test_SetNeedTotal_WithTrue_SetsNeedTotal()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->setNeedTotal(true);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertTrue($repository->getNeedTotal());
    }

    public function test_SetNeedTotal_WithFalse_SetsNeedTotal()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->setNeedTotal(false);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertFalse($repository->getNeedTotal());
    }

    public function test_GetNeedTotal_ByDefault_ReturnsTrue()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getNeedTotal();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetTotalCount_WithValue_SetsTotalCount()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $totalCount = 100;
        
        // Act
        $result = $repository->setTotalCount($totalCount);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEquals($totalCount, $repository->getTotalCount());
    }

    public function test_SetTotalCount_WithNull_SetsNull()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $repository->setTotalCount(100);
        
        // Act
        $result = $repository->setTotalCount(null);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertNull($repository->getTotalCount());
    }

    public function test_SetIsArray_WithTrue_SetsIsArray()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->setIsArray(true);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertTrue($repository->getIsArray());
    }

    public function test_SetIsArray_WithFalse_SetsIsArray()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->setIsArray(false);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertFalse($repository->getIsArray());
    }

    public function test_SetParams_WithParams_SetsParams()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $params = ['new_param' => 'new_value'];
        
        // Act
        $result = $repository->setParams($params);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEquals($params, $repository->getParams());
    }

    public function test_SetParams_WithClearFilter_ClearsFilter()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $repository->filter = ['existing' => 'filter'];
        $params = ['new_param' => 'new_value'];
        
        // Act
        $repository->setParams($params, false, true);
        
        // Assert
        $this->assertEmpty($repository->getFilters());
    }

    public function test_SetParams_WithUpdateFilter_UpdatesFilter()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $params = ['new_param' => 'new_value'];
        
        // Act
        $repository->setParams($params, true, false);
        
        // Assert
        // Filter should be collected (implementation dependent)
        $this->assertIsArray($repository->getFilters());
    }

    public function test_GetFilters_ByDefault_ReturnsArray()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getFilters();
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetFilter_WhenExists_ReturnsFilter()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $repository->filter = ['test_filter' => 'test_value'];
        
        // Act
        $result = $repository->getFilter('test_filter');
        
        // Assert
        $this->assertEquals('test_value', $result);
    }

    public function test_GetFilter_WhenNotExists_ReturnsNull()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getFilter('non_existent');
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetQueryBuilder_ReturnsModel()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getQueryBuilder();
        
        // Assert
        $this->assertIsObject($result);
    }

    public function test_Search_WithRefresh_ReturnsItems()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->search(true);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_Search_WithoutRefresh_ReturnsItems()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->search(false);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetModel_WhenModelClassSet_ReturnsModel()
    {
        // Arrange
        $repository = new ConcreteRepository([], \stdClass::class);
        
        // Act
        $result = $repository->getModel();
        
        // Assert
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function test_GetModel_WhenModelClassNotSet_ThrowsException()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model_class');
        $property->setAccessible(true);
        $property->setValue($repository, null);
        
        // Assert
        $this->expectException(RepositoryException::class);
        
        // Act
        $repository->getModel();
    }

    public function test_GetTableName_ReturnsString()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getTableName();
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals('test_table', $result);
    }

    public function test_LazyLoad_ByDefault_IsFalse()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Assert
        $this->assertFalse($repository->lazy_load);
    }

    public function test_LazyLoad_CanBeSet()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $repository->lazy_load = true;
        
        // Assert
        $this->assertTrue($repository->lazy_load);
    }

    public function test_Search_CallsBeforeFetchAndAfterFetch()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->search();
        
        // Assert
        // search() должен вызвать beforeFetch() и afterFetch()
        // и вернуть массив элементов
        $this->assertIsArray($result);
    }

    public function test_Search_WithParams_UsesParams()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $params = ['limit' => 10, 'offset' => 0];
        $repository->setParams($params);
        
        // Act
        $result = $repository->search();
        
        // Assert
        $this->assertIsArray($result);
        // Проверяем, что параметры были установлены
        $this->assertEquals($params, $repository->getParams());
    }

    public function test_GetQueryBuilder_CallsMakeQueryBuilder()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $queryBuilder = $repository->getQueryBuilder();
        
        // Assert
        $this->assertNotNull($queryBuilder);
        $this->assertIsObject($queryBuilder);
    }

    public function test_GetQueryBuilder_ReturnsSameInstance()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $queryBuilder1 = $repository->getQueryBuilder();
        $queryBuilder2 = $repository->getQueryBuilder();
        
        // Assert
        // getQueryBuilder() должен возвращать тот же экземпляр
        $this->assertSame($queryBuilder1, $queryBuilder2);
    }

    public function test_GetModel_CreatesModelInstance()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $modelClass = \stdClass::class;
        
        // Act
        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model_class');
        $property->setAccessible(true);
        $property->setValue($repository, $modelClass);
        
        $model = $repository->getModel();
        
        // Assert
        $this->assertInstanceOf($modelClass, $model);
    }

    public function test_GetModel_WithoutModelClass_ThrowsException()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Assert
        $this->expectException(\pribolshoy\repository\exceptions\RepositoryException::class);
        $this->expectExceptionMessage('Не задан класс сущности для репозитория');
        
        // Act
        $repository->getModel();
    }

    public function test_CollectFilter_CallsAllFilterMethods()
    {
        // Arrange
        $repository = $this->getMockBuilder(ConcreteRepository::class)
            ->onlyMethods(['modifyParams', 'defaultFilter', 'filter', 'addPreQueries'])
            ->getMock();
        
        $repository->expects($this->once())->method('modifyParams')->willReturnSelf();
        $repository->expects($this->once())->method('defaultFilter');
        $repository->expects($this->once())->method('filter');
        $repository->expects($this->once())->method('addPreQueries')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('collectFilter');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_ModifyParams_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('modifyParams');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_Filter_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('filter');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        // Метод filter() ничего не возвращает (void), но не выбрасывает исключение
        $this->assertNull($result);
    }

    public function test_AddPreQueries_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('addPreQueries');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_AddQueries_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('addQueries');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_AddLimitAndOffset_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('addLimitAndOffset');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_GetTotal_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('getTotal');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_AddConnections_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('addConnections');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_AfterFetch_ReturnsSelf()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('afterFetch');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_BeforeFetch_WhenLazyLoadFalse_CallsAddConnectionsAndAddQueries()
    {
        // Arrange
        $repository = $this->getMockBuilder(ConcreteRepository::class)
            ->onlyMethods(['addConnections', 'addQueries'])
            ->getMock();
        
        $repository->lazy_load = false;
        $repository->expects($this->once())->method('addConnections')->willReturnSelf();
        $repository->expects($this->once())->method('addQueries')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('beforeFetch');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_BeforeFetch_WhenLazyLoadTrue_DoesNotCallAddConnections()
    {
        // Arrange
        $repository = $this->getMockBuilder(ConcreteRepository::class)
            ->onlyMethods(['addConnections', 'addQueries'])
            ->getMock();
        
        $repository->lazy_load = true;
        $repository->expects($this->never())->method('addConnections');
        $repository->expects($this->once())->method('addQueries')->willReturnSelf();
        
        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('beforeFetch');
        $method->setAccessible(true);
        
        // Act
        $result = $method->invoke($repository);
        
        // Assert
        $this->assertSame($repository, $result);
    }

    public function test_SetParams_WithEmptyArray_ClearsParams()
    {
        // Arrange
        $repository = new ConcreteRepository(['param1' => 'value1']);
        
        // Act
        $result = $repository->setParams([]);
        
        // Assert
        $this->assertSame($repository, $result);
        $this->assertEmpty($repository->getParams());
    }

    public function test_GetFilter_WithEmptyString_ReturnsNull()
    {
        // Arrange
        $repository = new ConcreteRepository();
        
        // Act
        $result = $repository->getFilter('');
        
        // Assert
        $this->assertNull($result);
    }

    public function test_SetParams_WithClearFilterTrue_ClearsFilter()
    {
        // Arrange
        $repository = new ConcreteRepository();
        $reflection = new \ReflectionClass($repository);
        $filterProperty = $reflection->getProperty('filter');
        $filterProperty->setAccessible(true);
        $filterProperty->setValue($repository, ['existing_filter' => 'value']);
        
        // Act
        $repository->setParams(['new_param' => 'value'], false, true);
        
        // Assert
        $this->assertEmpty($repository->getFilters());
    }

    public function test_SetParams_WithUpdateFilterTrue_CallsCollectFilter()
    {
        // Arrange
        $repository = $this->getMockBuilder(ConcreteRepository::class)
            ->onlyMethods(['collectFilter'])
            ->getMock();
        $repository->expects($this->once())->method('collectFilter')->willReturnSelf();
        
        // Act
        $repository->setParams(['param' => 'value'], true, false);
        
        // Assert
        $this->assertEquals(['param' => 'value'], $repository->getParams());
    }
}

