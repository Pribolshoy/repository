<?php

namespace pribolshoy\repository\tests\traits;

use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\structures\AbstractStructure;
use pribolshoy\repository\tests\CommonTestCase;

class TestStructureWithHashable extends AbstractStructure
{
    use \pribolshoy\repository\traits\HashableStructure;
    
    protected ?string $keyName = null;
    
    protected function getKeyName(): ?string
    {
        return $this->keyName;
    }
    
    public function setKeyName(?string $keyName): void
    {
        $this->keyName = $keyName;
    }
    
    public function testGetHash($value): string
    {
        return $this->getHash($value);
    }
}

class HashableStructureTest extends CommonTestCase
{
    public function test_GetHash_WithString_ReturnsHashedString()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('hash')
            ->willReturnCallback(function($value) {
                return md5($value);
            });
        
        $structure = new TestStructureWithHashable($service);
        $testString = 'test_string';
        
        // Act
        $result = $structure->testGetHash($testString);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(md5($testString), $result);
    }

    public function test_GetHash_WithInteger_ReturnsHashedString()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('hash')
            ->willReturnCallback(function($value) {
                return md5((string)$value);
            });
        
        $structure = new TestStructureWithHashable($service);
        $testInt = 12345;
        
        // Act
        $result = $structure->testGetHash($testInt);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(md5((string)$testInt), $result);
    }

    public function test_GetHash_WithArray_ReturnsHashedItem()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemHash')
            ->willReturn('hashed_item');
        
        $structure = new TestStructureWithHashable($service);
        $testArray = ['id' => 1, 'name' => 'test'];
        
        // Act
        $result = $structure->testGetHash($testArray);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals('hashed_item', $result);
    }

    public function test_GetHash_WithArrayAndKeyName_ReturnsHashedAttribute()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemAttribute')
            ->willReturn('attribute_value');
        $service->method('hash')
            ->willReturnCallback(function($value) {
                return md5($value);
            });
        
        $structure = new TestStructureWithHashable($service);
        $structure->setKeyName('id');
        $testArray = ['id' => 1, 'name' => 'test'];
        
        // Act
        $result = $structure->testGetHash($testArray);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(md5('attribute_value'), $result);
    }

    public function test_GetHash_WithObject_ReturnsHashedItem()
    {
        // Arrange
        $service = $this->createMock(ServiceInterface::class);
        $service->method('getItemHash')
            ->willReturn('hashed_object');
        
        $structure = new TestStructureWithHashable($service);
        $testObject = (object)['id' => 1, 'name' => 'test'];
        
        // Act
        $result = $structure->testGetHash($testObject);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals('hashed_object', $result);
    }
}

