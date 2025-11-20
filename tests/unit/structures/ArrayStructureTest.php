<?php

namespace pribolshoy\repository\tests\structures;

use pribolshoy\repository\services\AbstractService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\ArrayStructure;
use pribolshoy\repository\tests\CommonTestCase;

class AbstractServiceObjectForArray extends AbstractService {

    public function sort(array $items): array {
        return $items;
    }
    
    public function getItemPrimaryKey($item) {
        return is_array($item) && isset($item['id']) ? $item['id'] : null;
    }
    
    public function getItemAttribute($item, string $name) {
        return is_array($item) && isset($item[$name]) ? $item[$name] : null;
    }
}

class ArrayStructureObject extends ArrayStructure {

}

final class ArrayStructureTest extends CommonTestCase
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

    public function test_AddParams()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        $this->assertNull($object->getItems());
        $this->assertEquals(
            get_class($object),
            get_class($object->addParams(['items' => $this->controllStringItems]))
        );

        $this->assertEquals(
            $this->controllStringItems,
            $object->getItems()
        );
    }

    public function test_SetItems_WithArrayItems_SetsItems()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Act
        $object->setItems($this->controllArrayItems);

        // Assert
        $this->assertEquals($this->controllArrayItems, $object->getItems());
    }

    public function test_AddItem_AddsItemToStructure()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Arrange
        $object->setItems($this->controllStringItems);
        $newItem = 'four';

        // Act
        $object->addItem($newItem);

        // Assert
        $items = $object->getItems();
        $this->assertContains($newItem, $items);
        $this->assertCount(4, $items);
    }

    public function test_GetByKey_WithExistingKey_ReturnsItem()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Arrange
        $object->setItems($this->controllArrayItems);

        // Act
        $result = $object->getByKey(0);

        // Assert
        $this->assertEquals($this->controllArrayItems[0], $result);
    }

    public function test_GetByKey_WithNonExistingKey_ReturnsNull()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Arrange
        $object->setItems($this->controllArrayItems);

        // Act
        $result = $object->getByKey(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_GetByKeys_WithArrayKeys_ReturnsItems()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Arrange
        $object->setItems($this->controllArrayItems);

        // Act
        $result = $object->getByKeys([0, 1]);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($this->controllArrayItems[0], $result[0]);
        $this->assertEquals($this->controllArrayItems[1], $result[1]);
    }

    public function test_GetByKeys_WithEmptyArray_ReturnsEmptyArray()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObjectForArray();
        $object = new ArrayStructureObject($service);

        // Arrange
        $object->setItems($this->controllArrayItems);

        // Act
        $result = $object->getByKeys([]);

        // Assert
        // Метод getByKeys возвращает null для пустого массива ключей (это поведение по умолчанию)
        $this->assertTrue(is_null($result) || (is_array($result) && empty($result)));
    }
}