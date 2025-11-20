<?php

namespace pribolshoy\repository\tests\drivers;

use pribolshoy\repository\drivers\AbstractCacheDriver;
use pribolshoy\repository\tests\CommonTestCase;

class ConcreteCacheDriver extends AbstractCacheDriver
{
    private array $storage = [];

    public function get(string $key, array $params = [])
    {
        return $this->storage[$key] ?? null;
    }

    public function set(string $key, $value, int $cache_duration = 0, array $params = []): object
    {
        $this->storage[$key] = $value;
        return $this;
    }

    public function delete(string $key, array $params = []): object
    {
        unset($this->storage[$key]);
        return $this;
    }
}

class AbstractCacheDriverTest extends CommonTestCase
{
    public function test_Constructor_WithParams_SetsProperties()
    {
        // Arrange
        $params = ['component' => 'memcached'];
        
        // Act
        $driver = new ConcreteCacheDriver($params);
        
        // Assert
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('component');
        $property->setAccessible(true);
        $this->assertEquals('memcached', $property->getValue($driver));
    }

    public function test_Get_WhenKeyExists_ReturnsValue()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $driver->set('test_key', 'test_value');
        
        // Act
        $result = $driver->get('test_key');
        
        // Assert
        $this->assertEquals('test_value', $result);
    }

    public function test_Get_WhenKeyNotExists_ReturnsNull()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        
        // Act
        $result = $driver->get('nonexistent_key');
        
        // Assert
        $this->assertNull($result);
    }

    public function test_Set_WithKeyAndValue_StoresValue()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $key = 'test_key';
        $value = 'test_value';
        
        // Act
        $result = $driver->set($key, $value);
        
        // Assert
        $this->assertSame($driver, $result);
        $this->assertEquals($value, $driver->get($key));
    }

    public function test_Set_WithCacheDuration_StoresValue()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $key = 'test_key';
        $value = 'test_value';
        $duration = 3600;
        
        // Act
        $result = $driver->set($key, $value, $duration);
        
        // Assert
        $this->assertSame($driver, $result);
        $this->assertEquals($value, $driver->get($key));
    }

    public function test_Delete_WhenKeyExists_RemovesKey()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $driver->set('test_key', 'test_value');
        
        // Act
        $result = $driver->delete('test_key');
        
        // Assert
        $this->assertSame($driver, $result);
        $this->assertNull($driver->get('test_key'));
    }

    public function test_Delete_WhenKeyNotExists_ReturnsDriver()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        
        // Act
        $result = $driver->delete('nonexistent_key');
        
        // Assert
        $this->assertSame($driver, $result);
    }

    public function test_Serialize_WithData_ReturnsSerializedString()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $data = ['key' => 'value', 'number' => 123];
        
        // Act
        $reflection = new \ReflectionClass($driver);
        $method = $reflection->getMethod('serialize');
        $method->setAccessible(true);
        $result = $method->invoke($driver, $data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(serialize($data), $result);
    }

    public function test_Unserialize_WithSerializedData_ReturnsUnserializedData()
    {
        // Arrange
        $driver = new ConcreteCacheDriver();
        $data = ['key' => 'value', 'number' => 123];
        $serialized = serialize($data);
        
        // Act
        $reflection = new \ReflectionClass($driver);
        $method = $reflection->getMethod('unserialize');
        $method->setAccessible(true);
        $result = $method->invoke($driver, $serialized);
        
        // Assert
        $this->assertEquals($data, $result);
    }
}

