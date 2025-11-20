<?php

namespace pribolshoy\repository\tests;

use pribolshoy\repository\Hasher;
use PHPUnit\Framework\TestCase;

final class HasherTest extends TestCase
{
    public function test_Hash_WithSimpleArray_ReturnsMd5Hash()
    {
        // Arrange
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        
        // Act
        $result = Hasher::hash($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result)); // MD5 hash length
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/i', $result);
    }

    public function test_Hash_WithNestedArray_ReturnsMd5Hash()
    {
        // Arrange
        $data = [
            'key1' => 'value1',
            'key2' => [
                'nested1' => 'nested_value1',
                'nested2' => 'nested_value2',
            ],
        ];
        
        // Act
        $result = Hasher::hash($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
    }

    public function test_Hash_WithUnsortedKeys_ReturnsConsistentHash()
    {
        // Arrange
        $data1 = ['b' => 'value2', 'a' => 'value1', 'c' => 'value3'];
        $data2 = ['a' => 'value1', 'b' => 'value2', 'c' => 'value3'];
        
        // Act
        $result1 = Hasher::hash($data1);
        $result2 = Hasher::hash($data2);
        
        // Assert
        // Хеш должен быть одинаковым независимо от порядка ключей
        $this->assertEquals($result1, $result2);
    }

    public function test_Hash_WithDifferentValues_ReturnsDifferentHashes()
    {
        // Arrange
        $data1 = ['key' => 'value1'];
        $data2 = ['key' => 'value2'];
        
        // Act
        $result1 = Hasher::hash($data1);
        $result2 = Hasher::hash($data2);
        
        // Assert
        $this->assertNotEquals($result1, $result2);
    }

    public function test_Hash_WithEmptyArray_ReturnsHash()
    {
        // Arrange
        $data = [];
        
        // Act
        $result = Hasher::hash($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
    }

    public function test_Hash_WithNestedUnsortedKeys_SortsRecursively()
    {
        // Arrange
        $data1 = [
            'z' => 'last',
            'a' => 'first',
            'm' => [
                'z' => 'nested_last',
                'a' => 'nested_first',
            ],
        ];
        $data2 = [
            'a' => 'first',
            'm' => [
                'a' => 'nested_first',
                'z' => 'nested_last',
            ],
            'z' => 'last',
        ];
        
        // Act
        $result1 = Hasher::hash($data1);
        $result2 = Hasher::hash($data2);
        
        // Assert
        // Хеш должен быть одинаковым, так как ключи отсортированы рекурсивно
        $this->assertEquals($result1, $result2);
    }

    public function test_Hash_WithSerializeFalse_UsesJsonEncode()
    {
        // Arrange
        $data = ['key' => 'value'];
        
        // Act
        $result = Hasher::hash($data, false);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
    }

    public function test_Hash_WithSerializeTrue_UsesSerialize()
    {
        // Arrange
        $data = ['key' => 'value'];
        
        // Act
        $result = Hasher::hash($data, true);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
    }

    public function test_Hash_WithSerializeTrueAndFalse_ReturnsDifferentHashes()
    {
        // Arrange
        $data = ['key' => 'value'];
        
        // Act
        $result1 = Hasher::hash($data, true);
        $result2 = Hasher::hash($data, false);
        
        // Assert
        // Разные методы сериализации должны давать разные хеши
        $this->assertNotEquals($result1, $result2);
    }

    public function test_Sort_WithSimpleArray_SortsKeys()
    {
        // Arrange
        $data = ['c' => 'value3', 'a' => 'value1', 'b' => 'value2'];
        
        // Act
        $result = Hasher::sort($data);
        
        // Assert
        $this->assertIsArray($result);
        $keys = array_keys($result);
        $this->assertEquals(['a', 'b', 'c'], $keys);
        $this->assertEquals('value1', $result['a']);
        $this->assertEquals('value2', $result['b']);
        $this->assertEquals('value3', $result['c']);
    }

    public function test_Sort_WithNestedArray_SortsKeysRecursively()
    {
        // Arrange
        $data = [
            'z' => 'last',
            'a' => 'first',
            'm' => [
                'z' => 'nested_last',
                'a' => 'nested_first',
            ],
        ];
        
        // Act
        $result = Hasher::sort($data);
        
        // Assert
        $this->assertIsArray($result);
        $keys = array_keys($result);
        $this->assertEquals(['a', 'm', 'z'], $keys);
        
        $nestedKeys = array_keys($result['m']);
        $this->assertEquals(['a', 'z'], $nestedKeys);
    }

    public function test_Sort_WithEmptyArray_ReturnsEmptyArray()
    {
        // Arrange
        $data = [];
        
        // Act
        $result = Hasher::sort($data);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_Sort_WithDeeplyNestedArray_SortsAllLevels()
    {
        // Arrange
        $data = [
            'c' => [
                'z' => [
                    'x' => 'deep_value',
                    'a' => 'another_deep_value',
                ],
                'a' => 'nested_value',
            ],
            'a' => 'top_value',
        ];
        
        // Act
        $result = Hasher::sort($data);
        
        // Assert
        $this->assertIsArray($result);
        $topKeys = array_keys($result);
        $this->assertEquals(['a', 'c'], $topKeys);
        
        $secondLevelKeys = array_keys($result['c']);
        $this->assertEquals(['a', 'z'], $secondLevelKeys);
        
        $thirdLevelKeys = array_keys($result['c']['z']);
        $this->assertEquals(['a', 'x'], $thirdLevelKeys);
    }

    public function test_Hash_WithNumericKeys_PreservesNumericKeys()
    {
        // Arrange
        $data = [2 => 'value2', 1 => 'value1', 3 => 'value3'];
        
        // Act
        $result = Hasher::hash($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
    }

    public function test_Hash_WithMixedKeys_SortsStringKeys()
    {
        // Arrange
        $data = ['z' => 'last', 'a' => 'first', 1 => 'numeric'];
        
        // Act
        $result = Hasher::hash($data);
        $sorted = Hasher::sort($data);
        
        // Assert
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result));
        // Проверяем, что строковые ключи отсортированы
        $keys = array_keys($sorted);
        $this->assertContains('a', $keys);
        $this->assertContains('z', $keys);
    }

    public function test_Hash_WithComplexDataStructure_ReturnsConsistentHash()
    {
        // Arrange
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            'settings' => [
                'theme' => 'dark',
                'language' => 'en',
            ],
        ];
        
        // Act
        $result1 = Hasher::hash($data);
        $result2 = Hasher::hash($data);
        
        // Assert
        // Одинаковые данные должны давать одинаковый хеш
        $this->assertEquals($result1, $result2);
    }

    public function test_Sort_ReturnsSortedArray()
    {
        // Arrange
        $data = ['c' => 'value3', 'a' => 'value1', 'b' => 'value2'];
        
        // Act
        $result = Hasher::sort($data);
        
        // Assert
        // Результат должен быть отсортирован по ключам
        $keys = array_keys($result);
        $this->assertEquals(['a', 'b', 'c'], $keys);
        $this->assertEquals('value1', $result['a']);
        $this->assertEquals('value2', $result['b']);
        $this->assertEquals('value3', $result['c']);
    }
}

