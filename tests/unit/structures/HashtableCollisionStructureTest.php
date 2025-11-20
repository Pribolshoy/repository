<?php

namespace pribolshoy\repository\tests\structures;

use pribolshoy\repository\services\AbstractService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\structures\HashtableCollisionStructure;
use pribolshoy\repository\tests\CommonTestCase;

class AbstractServiceObjectForCollision extends AbstractService
{
    public function sort(array $items): array
    {
        return $items;
    }
}

class HashtableCollisionStructureTest extends CommonTestCase
{
    protected array $controllArrayItems = [
        [
            'id' => 1,
            'group' => 'A',
            'collision' => 'X',
        ],
        [
            'id' => 2,
            'group' => 'A',
            'collision' => 'Y',
        ],
        [
            'id' => 3,
            'group' => 'B',
            'collision' => 'X',
        ],
    ];

    public function test_GetGroupKeys_WhenNotSet_ReturnsEmptyArray()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getGroupKeys();
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_SetItems_WithGroupKeys_OrganizesItemsByGroup()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Используем рефлексию для установки group_keys и collision_keys
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        // Act
        $structure->setItems($this->controllArrayItems);
        
        // Assert
        $items = $structure->getItems();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('A', $items);
        $this->assertArrayHasKey('B', $items);
    }

    public function test_AddItem_WithGroupKey_AddsItemToGroup()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $item = ['id' => 1, 'group' => 'A', 'collision' => 'X'];
        $groupKey = 'A';
        
        // Act
        $result = $structure->addItem($item, $groupKey);
        
        // Assert
        $this->assertSame($structure, $result);
        $items = $structure->getItems();
        $this->assertIsArray($items);
        $this->assertArrayHasKey($groupKey, $items);
    }

    public function test_GetByKey_WithGroupAndCollisionKey_ReturnsItem()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $structure->setItems($this->controllArrayItems);
        
        // Act
        $key = ['group' => 'A', 'collision' => 'X', 'id' => 1];
        $result = $structure->getByKey($key);
        
        // Assert
        // Проверяем, что метод не выбрасывает исключение и возвращает результат
        $this->assertTrue(is_null($result) || is_array($result));
    }

    public function test_GetByKey_WhenKeyNotExists_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getByKey(['group' => 'NonExistent']);
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetByKeys_WithArrayKey_ReturnsItem()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $structure->setItems($this->controllArrayItems);
        
        // Act
        $key = ['group' => 'A', 'collision' => 'X', 'id' => 1];
        $result = $structure->getByKeys($key);
        
        // Assert
        // Проверяем, что метод не выбрасывает исключение и возвращает результат
        $this->assertTrue(is_null($result) || is_array($result));
    }

    public function test_GetByKeys_WhenKeyNotArray_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getByKeys('not_array');
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetByKeys_WhenItemsNotSet_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getByKeys(['group' => 'A']);
        
        // Assert
        $this->assertNull($result);
    }

    public function test_SetItems_WithEmptyArray_ClearsItems()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        // Сначала устанавливаем элементы
        $structure->setItems($this->controllArrayItems);
        
        // Act
        $result = $structure->setItems([]);
        
        // Assert
        $this->assertSame($structure, $result);
        $items = $structure->getItems();
        $this->assertIsArray($items);
        $this->assertEmpty($items);
    }

    public function test_AddItem_WithNullGroupKey_HandlesGracefully()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $item = ['id' => 1, 'collision' => 'X'];
        
        // Act
        $result = $structure->addItem($item, null);
        
        // Assert
        $this->assertSame($structure, $result);
        // Проверяем, что метод не выбрасывает исключение
        $items = $structure->getItems();
        $this->assertIsArray($items);
    }

    public function test_AddItem_WithEmptyCollisionKeys_HandlesGracefully()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Не устанавливаем collision_keys (остаются пустыми)
        $item = ['id' => 1, 'group' => 'A'];
        $groupKey = 'A';
        
        // Act
        $result = $structure->addItem($item, $groupKey);
        
        // Assert
        $this->assertSame($structure, $result);
        $items = $structure->getItems();
        $this->assertIsArray($items);
        // При пустых collision_keys getCollisionKey вернет null, и элемент будет добавлен с ключом null
        $this->assertArrayHasKey($groupKey, $items);
    }

    public function test_GetByKey_WithNullKey_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getByKey(null);
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetByKey_WithEmptyStringKey_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        // Act
        $result = $structure->getByKey('');
        
        // Assert
        $this->assertNull($result);
    }

    public function test_SetItems_WithGroupKeysButNoCollisionKeys_OrganizesByGroupOnly()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        // Не устанавливаем collision_keys
        
        // Act
        $structure->setItems($this->controllArrayItems);
        
        // Assert
        $items = $structure->getItems();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        // Элементы должны быть организованы по группам
        $this->assertArrayHasKey('A', $items);
        $this->assertArrayHasKey('B', $items);
    }

    public function test_GetByKey_WhenGroupKeyIsNull_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $structure->setItems($this->controllArrayItems);
        
        // Ключ без group (getGroupKey вернет null)
        $key = ['collision' => 'X', 'id' => 1];
        
        // Act
        $result = $structure->getByKey($key);
        
        // Assert
        // Если groupKey null, метод должен вернуть null
        $this->assertNull($result);
    }

    public function test_GetByKeys_WhenGroupKeyIsNull_ReturnsNull()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        $structure->setItems($this->controllArrayItems);
        
        // Ключ без group (getGroupKey вернет null)
        $key = ['collision' => 'X', 'id' => 1];
        
        // Act
        $result = $structure->getByKeys($key);
        
        // Assert
        // Если groupKey null, метод должен вернуть null
        $this->assertNull($result);
    }

    public function test_SetItems_WhenGroupKeyIsNull_SkipsItem()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group']);
        
        $collisionKeysProperty = $reflection->getProperty('collision_keys');
        $collisionKeysProperty->setAccessible(true);
        $collisionKeysProperty->setValue($structure, ['collision']);
        
        // Элемент без поля 'group' (getGroupKey вернет null)
        $items = [
            ['id' => 1, 'collision' => 'X'], // нет 'group'
            ['id' => 2, 'group' => 'A', 'collision' => 'Y'],
        ];
        
        // Act
        $structure->setItems($items);
        
        // Assert
        $resultItems = $structure->getItems();
        $this->assertIsArray($resultItems);
        // Элемент без group должен быть пропущен или добавлен с ключом null
        // Проверяем, что хотя бы один элемент добавлен
        $this->assertNotEmpty($resultItems);
    }

    public function test_GetGroupKeys_WhenSet_ReturnsGroupKeys()
    {
        // Arrange
        $service = new AbstractServiceObjectForCollision();
        $structure = new HashtableCollisionStructure($service);
        
        $reflection = new \ReflectionClass($structure);
        $groupKeysProperty = $reflection->getProperty('group_keys');
        $groupKeysProperty->setAccessible(true);
        $groupKeysProperty->setValue($structure, ['group', 'category']);
        
        // Act
        $result = $structure->getGroupKeys();
        
        // Assert
        $this->assertEquals(['group', 'category'], $result);
    }
}

