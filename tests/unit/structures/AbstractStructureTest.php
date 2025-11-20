<?php

namespace pribolshoy\repository\tests\structures;

use pribolshoy\repository\services\AbstractService;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\structures\AbstractStructure;
use pribolshoy\repository\tests\CommonTestCase;

class AbstractServiceObject extends AbstractService {

    public function sort(array $items): array {}
}

class AbstractStructureObject extends AbstractStructure {
    public ?string $someProperty = null;
}

class AbstractStructureTest extends CommonTestCase
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
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        $this->assertNull($object->getItems());
        $this->assertInstanceOf(
            AbstractStructure::class,
            $object->addParams([
                'items'         => $this->controllStringItems,
                'someProperty'  => 'testValue',
            ])
        );

        $this->assertEquals($this->controllStringItems,
            $object->getItems()
        );

        $this->assertEquals('testValue', $object->someProperty);
    }

    public function test_GetItems()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        $this->assertNull($object->getItems());
    }

    public function test_SetItems()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        $this->assertNull($object->getItems());

        $object->setItems($this->controllStringItems);

        $this->assertIsArray($items = $object->getItems());
        $this->assertNotEmpty($items);
        $this->assertEquals($this->controllStringItems, $items);
    }

    public function test_AddItem()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        // testing of just item adding
        $object
            ->setItems($this->controllStringItems)
            ->addItem('four');

        $this->assertEquals(array_merge($this->controllStringItems, ['four']), $object->getItems());

        // testing of..
        $itemForReplacing = 'five';
        $oldItems = $object->getItems();

        $object
            ->addItem('five', 1);

        $this->assertNotEquals($oldItems[1], $object->getItems()[1]);
        $this->assertEquals($itemForReplacing, $object->getItems()[1]);
    }

    public function test_GetByKey()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        $this->assertNull($object->getByKey('some_key'));

        $items = $object
            ->setItems($this->controllArrayItems)
            ->getItems();

        foreach ($items as $key => $item) {
            $this->assertEquals($item, $object->getByKey($key));
        }

        // not existing
        $this->assertNull($object->getByKey(''));
        $this->assertNull($object->getByKey(8));
    }

    public function test_GetByKeys()
    {
        /** @var ServiceInterface $object */
        /** @var StructureInterface $object */
        $service = new AbstractServiceObject();
        $object = new AbstractStructureObject($service);

        $this->assertNull($object->getByKeys(['some_key_1', 'some_key_2']));

        $object
            ->setItems($this->controllArrayItems);

        // only existing
        $result = $object->getByKeys([0, 2]);
        $this->assertCount(2, $result);

        // with no existing
        $result = $object->getByKeys([0, 2, 'some_key_1', 'some_key_2']);
        $this->assertCount(2, $result);

        // Метод getByKeys возвращает null для пустого массива или несуществующих ключей
        // или пустой массив, если array_intersect_key вернул пустой массив
        $result1 = $object->getByKeys(['']);
        $this->assertTrue(is_null($result1) || (is_array($result1) && empty($result1)));
        
        $result2 = $object->getByKeys([8]);
        $this->assertTrue(is_null($result2) || (is_array($result2) && empty($result2)));
    }
}