<?php

namespace pribolshoy\repository\tests\traits;

use pribolshoy\repository\tests\CommonTestCase;

class TestClassWithCatalogTrait
{
    use \pribolshoy\repository\traits\CatalogTrait;
}

class CatalogTraitTest extends CommonTestCase
{
    public function test_SetPages_WithPages_SetsPages()
    {
        // Arrange
        $object = new TestClassWithCatalogTrait();
        $pages = new \stdClass();
        
        // Act
        $result = $object->setPages($pages);
        
        // Assert
        $this->assertSame($object, $result);
        $this->assertSame($pages, $object->getPages());
    }

    public function test_GetPages_WhenNotSet_ReturnsNull()
    {
        // Arrange
        $object = new TestClassWithCatalogTrait();
        
        // Act
        $result = $object->getPages();
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetPages_WhenSet_ReturnsPages()
    {
        // Arrange
        $object = new TestClassWithCatalogTrait();
        $pages = new \stdClass();
        $object->setPages($pages);
        
        // Act
        $result = $object->getPages();
        
        // Assert
        $this->assertSame($pages, $result);
    }

    public function test_PageProperty_CanBeSet()
    {
        // Arrange
        $object = new TestClassWithCatalogTrait();
        $page = 5;
        
        // Act
        $object->page = $page;
        
        // Assert
        $this->assertEquals($page, $object->page);
        $this->assertIsInt($object->page);
    }

    public function test_PageProperty_HasDefaultValue()
    {
        // Arrange
        $object = new TestClassWithCatalogTrait();
        
        // Assert
        $this->assertEquals(0, $object->page);
    }
}

