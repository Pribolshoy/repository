<?php

namespace pribolshoy\repository\tests\traits;

use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\tests\CommonTestCase;

class TestClassWithUsedByServiceTrait
{
    use \pribolshoy\repository\traits\UsedByServiceTrait;
}

class UsedByServiceTraitTest extends CommonTestCase
{
    public function test_SetService_WithService_SetsService()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        $object = new TestClassWithUsedByServiceTrait();
        
        // Act
        $object->setService($service);
        
        // Assert
        $this->assertSame($service, $object->getService());
    }

    public function test_SetService_WithNull_SetsNull()
    {
        // Arrange
        $object = new TestClassWithUsedByServiceTrait();
        $object->setService($this->createMock(BaseServiceInterface::class));
        
        // Act
        $object->setService(null);
        
        // Assert
        $this->assertNull($object->getService());
    }

    public function test_GetService_WhenNotSet_ReturnsNull()
    {
        // Arrange
        $object = new TestClassWithUsedByServiceTrait();
        
        // Act
        $result = $object->getService();
        
        // Assert
        $this->assertNull($result);
    }

    public function test_GetService_WhenSet_ReturnsService()
    {
        // Arrange
        $service = $this->createMock(BaseServiceInterface::class);
        $object = new TestClassWithUsedByServiceTrait();
        $object->setService($service);
        
        // Act
        $result = $object->getService();
        
        // Assert
        $this->assertSame($service, $result);
        $this->assertInstanceOf(BaseServiceInterface::class, $result);
    }
}

