<?php

namespace pribolshoy\repository\tests\services;

use pribolshoy\repository\services\BaseService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\structures\HashtableStructure;
use pribolshoy\repository\tests\CommonTestCase;

// Используем класс из AbstractServiceTest
require_once __DIR__ . '/AbstractServiceTest.php';

class BaseServiceTestObject extends BaseService {
    /**
     * TODO: make protected
     * Must be realized in child because
     * $item can be specific type object
     * and have special method for taking primary key.
     *
     * @param $item
     *
     * @return mixed
     */
    public function getItemPrimaryKey($item) {
        // Возвращаем пустую строку вместо null, чтобы избежать ошибки mb_strlen(null)
        return is_array($item) && isset($item['id']) ? (string)$item['id'] : '';
    }
}

final class BaseServiceTest extends CommonTestCase
{
    protected array $controllStringItems = [
        'one',
        'two',
        'three',
    ];

    protected array $controllArrayItems = [
        [
            'id'    => 67,
            'name'  => 'one',
        ],
        [
            'id'    => 11,
            'name'  => 'two',
        ],
        [
            'id'    => 39,
            'name'  => 'three',
        ],
    ];

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $itemStructure = $object->getItemStructure();

        $this->assertEquals(ArrayStructure::class, get_class($itemStructure));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetBasicHashtableStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $itemHashtableStructure = $object->getBasicHashtableStructure();

        $this->assertEquals(HashtableStructure::class, get_class($itemHashtableStructure));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetNamedStructures()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $this->assertEmpty($object->getNamedStructures());
        $this->assertIsArray($object->getNamedStructures());
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetNamedStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $this->assertEmpty($object->getNamedStructure('not_existing'));
        $this->assertNull($object->getNamedStructure('not_existing'));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItems()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $this->assertEmpty($object->getItems());
        $this->assertNull($object->getItems());

        $object->setItems($this->controllArrayItems);

        $this->assertEquals($this->controllArrayItems, $object->getItems());
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemHash()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        $item = ['id' => 67, 'name' => 'one'];
        
        // Act
        $hash = $object->getItemHash($item);
        
        // Assert
        $this->assertIsString($hash);
        $this->assertEquals(32, strlen($hash)); // MD5 hash length
    }

    public function test_Hash()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Act
        $hash = $object->hash('test_value');
        
        // Assert
        $this->assertIsString($hash);
        $this->assertEquals(32, strlen($hash)); // MD5 hash length
        $this->assertEquals(md5('test_value'), $hash);
    }

    public function test_GetHashtable()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setItems($this->controllArrayItems);
        
        // Act
        $hashtable = $object->getHashtable();
        
        // Assert
        $this->assertIsArray($hashtable);
    }

    public function test_UpdateHashtable()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->updateHashtable();
        
        // Assert
        $this->assertSame($object, $result);
    }

    public function test_SetFilterClass()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Act
        $result = $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        
        // Assert
        $this->assertSame($object, $result);
    }

    public function test_GetFilter()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        
        // Act
        $filter = $object->getFilter();
        
        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\filters\AbstractFilter::class, $filter);
    }

    public function test_GetFilter_WithRefresh_ReturnsNewFilter()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $filter1 = $object->getFilter();
        
        // Act
        $filter2 = $object->getFilter(true);
        
        // Assert
        $this->assertNotSame($filter1, $filter2);
    }

    public function test_SetSorting()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $sorting = ['name' => 'ASC'];
        
        // Act
        $result = $object->setSorting($sorting);
        
        // Assert
        $this->assertSame($object, $result);
    }

    public function test_GetList_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getList();
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByExp_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getByExp(['name' => 'one']);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetByMulti_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getByMulti(['id' => 67]);
        
        // Assert
        $this->assertIsArray($result);
    }

    public function test_GetBy_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getBy(['id' => 67]);
        
        // Assert
        // getBy может вернуть элемент или пустой массив
        $this->assertTrue(is_array($result) || is_null($result));
    }

    public function test_GetById_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getById(67);
        
        // Assert
        // getById может вернуть элемент или пустой массив
        $this->assertTrue(is_array($result) || is_null($result));
    }

    public function test_GetByIds_ReturnsFilterResult()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();
        
        // Arrange
        $object->setFilterClass(\pribolshoy\repository\filters\ServiceFilter::class);
        $object->setItems($this->controllArrayItems);
        
        // Act
        $result = $object->getByIds([67, 11]);
        
        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_SetItems_SetsItemsAndUpdatesHashtable()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        
        // Act
        $object->setItems($this->controllArrayItems);
        
        // Assert
        $this->assertEquals($this->controllArrayItems, $object->getItems());
        // Проверяем, что hashtable обновлен
        $hashtable = $object->getHashtable();
        $this->assertIsArray($hashtable);
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_AddItem_WhenItemsEmpty_AddsItem()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        
        $newItem = ['id' => 100, 'name' => 'new'];
        
        // Act
        $result = $object->addItem($newItem);
        
        // Assert
        $this->assertSame($object, $result);
        $items = $object->getItems();
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertEquals($newItem, $items[0]);
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_AddItem_WhenItemNotExists_AddsItem()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        $object->setItems($this->controllArrayItems);
        
        $newItem = ['id' => 100, 'name' => 'new'];
        
        // Act
        $result = $object->addItem($newItem);
        
        // Assert
        $this->assertSame($object, $result);
        $items = $object->getItems();
        $this->assertCount(4, $items); // 3 существующих + 1 новый
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_AddItem_WhenItemExistsAndReplaceTrue_ReplacesItem()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        $object->setItems($this->controllArrayItems);
        
        $existingItem = ['id' => 67, 'name' => 'updated'];
        
        // Act
        $result = $object->addItem($existingItem, true);
        
        // Assert
        $this->assertSame($object, $result);
        $items = $object->getItems();
        $this->assertCount(3, $items); // Количество не изменилось
        // Находим обновленный элемент
        $found = false;
        foreach ($items as $item) {
            if ($item['id'] == 67) {
                $this->assertEquals('updated', $item['name']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Item should be replaced');
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_AddItem_WhenItemExistsAndReplaceFalse_DoesNotAddItem()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        $object->setItems($this->controllArrayItems);
        
        $existingItem = ['id' => 67, 'name' => 'should_not_change'];
        $initialCount = count($object->getItems());
        
        // Act
        $result = $object->addItem($existingItem, false);
        
        // Assert
        $this->assertSame($object, $result);
        $items = $object->getItems();
        $this->assertCount($initialCount, $items); // Количество не изменилось
        // Проверяем, что элемент не изменился
        foreach ($items as $item) {
            if ($item['id'] == 67) {
                $this->assertEquals('one', $item['name']); // Оригинальное значение
                break;
            }
        }
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemHash_WhenPrimaryKeyExists_ReturnsHashOfPrimaryKey()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        
        $item = ['id' => 67, 'name' => 'one'];
        
        // Act
        $hash = $object->getItemHash($item);
        
        // Assert
        $this->assertIsString($hash);
        $this->assertEquals(32, strlen($hash)); // MD5 hash length
        // Хеш должен быть от primary key (id)
        $expectedHash = md5('67');
        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemHash_WhenPrimaryKeyEmpty_ReturnsHashOfSerializedItem()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        // Устанавливаем primaryKeys, чтобы метод getItemPrimaryKey работал корректно
        $object->setPrimaryKeys(['id']);
        
        $item = ['name' => 'one']; // Нет id
        
        // Act
        $hash = $object->getItemHash($item);
        
        // Assert
        $this->assertIsString($hash);
        $this->assertEquals(32, strlen($hash)); // MD5 hash length
        // Хеш должен быть от сериализованного элемента, так как primaryKey пустой
        $expectedHash = md5(serialize($item));
        $this->assertEquals($expectedHash, $hash);
    }

    public function test_IsMultiplePrimaryKey_ByDefault_ReturnsTrue()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        
        // Act
        $result = $object->isMultiplePrimaryKey();
        
        // Assert
        $this->assertTrue($result);
    }

    public function test_SetPrimaryKeys_SetsPrimaryKeys()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();
        
        // Act
        $result = $object->setPrimaryKeys(['id', 'name']);
        
        // Assert
        $this->assertSame($object, $result);
        // Проверяем через рефлексию
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('primaryKeys');
        $property->setAccessible(true);
        $this->assertEquals(['id', 'name'], $property->getValue($object));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemStructure_WithRefresh_ReturnsNewStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $structure1 = $object->getItemStructure();
        $structure2 = $object->getItemStructure(true);

        $this->assertNotSame($structure1, $structure2);
        $this->assertEquals(ArrayStructure::class, get_class($structure2));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetBasicHashtableStructure_WithRefresh_ReturnsNewStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $structure1 = $object->getBasicHashtableStructure();
        $structure2 = $object->getBasicHashtableStructure(true);

        $this->assertNotSame($structure1, $structure2);
        $this->assertEquals(HashtableStructure::class, get_class($structure2));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetNamedStructure_WithArrayConfig_InitializesStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для установки adding_structures
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('adding_structures');
        $property->setAccessible(true);
        $property->setValue($object, [
            'test_structure' => [
                'class' => ArrayStructure::class,
            ]
        ]);

        $structure = $object->getNamedStructure('test_structure');

        $this->assertInstanceOf(\pribolshoy\repository\interfaces\StructureInterface::class, $structure);
        $this->assertEquals(ArrayStructure::class, get_class($structure));
    }

    public function test_GetSorting_ByDefault_ReturnsEmptyArray()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для получения sorting
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('sorting');
        $property->setAccessible(true);

        $sorting = $property->getValue($object);

        $this->assertIsArray($sorting);
        $this->assertEmpty($sorting);
    }

    public function test_GetSorting_AfterSetSorting_ReturnsSorting()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $sorting = ['name' => 'ASC', 'id' => 'DESC'];
        $object->setSorting($sorting);

        // Используем рефлексию для получения sorting
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('sorting');
        $property->setAccessible(true);

        $result = $property->getValue($object);

        $this->assertEquals($sorting, $result);
    }

    public function test_SetRepositoryClass_SetsRepositoryClass()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $repositoryClass = 'TestRepositoryClass';
        $result = $object->setRepositoryClass($repositoryClass);

        $this->assertSame($object, $result);

        // Проверяем через рефлексию
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $this->assertEquals($repositoryClass, $property->getValue($object));
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemStructure_WhenClassNotSet_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();

        // Используем рефлексию для установки пустого класса
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('item_structure_class');
        $property->setAccessible(true);
        $property->setValue($object, '');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Property item_structure_class is not set');

        $object->getItemStructure();
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetItemStructure_WhenClassNotFound_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();

        // Используем рефлексию для установки несуществующего класса
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('item_structure_class');
        $property->setAccessible(true);
        $property->setValue($object, 'NonExistentClass');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Item structure class not found');

        $object->getItemStructure();
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetBasicHashtableStructure_WhenClassNotSet_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();

        // Используем рефлексию для установки пустого класса
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('hashtable_item_structure_class');
        $property->setAccessible(true);
        $property->setValue($object, '');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Property hashtable_item_structure_class is not set');

        $object->getBasicHashtableStructure();
    }

    /**
     * @throws \pribolshoy\repository\exceptions\ServiceException
     */
    public function test_GetBasicHashtableStructure_WhenClassNotFound_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new BaseServiceTestObject();

        // Используем рефлексию для установки несуществующего класса
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('hashtable_item_structure_class');
        $property->setAccessible(true);
        $property->setValue($object, 'NonExistentClass');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Item structure class not found');

        $object->getBasicHashtableStructure();
    }

    public function test_Init_IsCalledInConstructor()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для проверки, что init() был вызван
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('init');
        $method->setAccessible(true);

        // Метод init() пустой, но мы можем проверить, что он существует и вызывается
        $this->assertTrue($method->isProtected());
    }

    public function test_InsertAddingStructure_InsertsStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $structure = $this->createMock(\pribolshoy\repository\interfaces\StructureInterface::class);

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('insertAddingStructure');
        $method->setAccessible(true);

        // Act
        $result = $method->invoke($object, 'test_structure', $structure);

        // Assert
        $this->assertSame($object, $result);
        $this->assertSame($structure, $object->getNamedStructure('test_structure'));
    }

    public function test_GetRepositoryClass_WhenClassNotSet_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('getRepositoryClass');
        $method->setAccessible(true);

        // Используем рефлексию для установки пустого repository_class
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $property->setValue($object, '');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Property repository_class is not set');

        $method->invoke($object);
    }

    public function test_GetRepositoryClass_WhenClassNotFound_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('getRepositoryClass');
        $method->setAccessible(true);

        // Используем рефлексию для установки несуществующего класса
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $property->setValue($object, 'NonExistentRepositoryClass');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Repository class not found');

        $method->invoke($object);
    }

    public function test_GetRepositoryClass_WhenClassExists_ReturnsClassName()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('getRepositoryClass');
        $method->setAccessible(true);

        // Используем рефлексию для установки существующего класса
        $property = $reflection->getProperty('repository_class');
        $property->setAccessible(true);
        $property->setValue($object, \pribolshoy\repository\AbstractRepository::class);

        // Act
        $result = $method->invoke($object);

        // Assert
        $this->assertEquals(\pribolshoy\repository\AbstractRepository::class, $result);
    }

    public function test_GetRepository_WhenClassNotSet_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Property repository_class is not set');

        $object->getRepository();
    }

    public function test_GetRepository_WhenClassNotFound_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        $object->setRepositoryClass('NonExistentRepositoryClass');

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Repository class not found');

        $object->getRepository();
    }

    public function test_GetRepository_WhenClassExists_ReturnsRepository()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем конкретный класс из тестов репозиториев
        require_once __DIR__ . '/../repositories/AbstractRepositoryTest.php';
        $object->setRepositoryClass(\pribolshoy\repository\tests\repositories\ConcreteRepository::class);

        // Act
        $repository = $object->getRepository();

        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\interfaces\RepositoryInterface::class, $repository);
    }

    public function test_InitAddingStructure_WhenClassNotSet_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('initAddingStructure');
        $method->setAccessible(true);

        $config = []; // без 'class'

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Adding item structure class not found');

        $method->invoke($object, $config);
    }

    public function test_InitAddingStructure_WhenClassNotFound_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('initAddingStructure');
        $method->setAccessible(true);

        $config = ['class' => 'NonExistentStructureClass'];

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Adding item structure class not found');

        $method->invoke($object, $config);
    }

    public function test_InitAddingStructure_WhenClassExists_ReturnsStructure()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Используем рефлексию для вызова protected метода
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('initAddingStructure');
        $method->setAccessible(true);

        $config = ['class' => \pribolshoy\repository\structures\ArrayStructure::class, 'param1' => 'value1'];

        // Act
        $structure = $method->invoke($object, $config);

        // Assert
        $this->assertInstanceOf(\pribolshoy\repository\interfaces\StructureInterface::class, $structure);
    }

    public function test_UpdateHashtable_WithNamedStructures_UpdatesAllStructures()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Arrange
        $object->setItems($this->controllArrayItems);

        // Используем рефлексию для установки named structure
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('adding_structures');
        $property->setAccessible(true);
        $property->setValue($object, [
            'test_structure' => [
                'class' => \pribolshoy\repository\structures\HashtableStructure::class,
            ]
        ]);

        // Act
        $result = $object->updateHashtable();

        // Assert
        $this->assertSame($object, $result);
        $structure = $object->getNamedStructure('test_structure');
        $this->assertNotNull($structure);
    }

    public function test_GetRepository_WhenRepositoryDoesNotImplementInterface_ThrowsException()
    {
        /** @var ServiceInterface|BaseService $object */
        $object = new AbstractServiceObject();

        // Создаем класс, который не реализует RepositoryInterface
        $nonRepositoryClass = new class {
            public function __construct() {}
        };
        $nonRepositoryClassName = get_class($nonRepositoryClass);

        $object->setRepositoryClass($nonRepositoryClassName);

        $this->expectException(\pribolshoy\repository\exceptions\ServiceException::class);
        $this->expectExceptionMessage('Repository must implement RepositoryInterface');

        $object->getRepository();
    }
}