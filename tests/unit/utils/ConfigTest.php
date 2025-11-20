<?php

namespace pribolshoy\repository\tests\utils;

use pribolshoy\repository\Config;
use pribolshoy\repository\tests\CommonTestCase;

class ConfigTest extends CommonTestCase
{
    protected function tearDown(): void
    {
        // Сбрасываем статические свойства после каждого теста
        Config::setStringDelimiter(':');
        Config::setHashDelimiter('=');
        parent::tearDown();
    }

    public function test_GetStringDelimiter_ByDefault_ReturnsDefault()
    {
        // Arrange & Act
        $result = Config::getStringDelimiter();
        
        // Assert
        $this->assertEquals(':', $result);
    }

    public function test_SetStringDelimiter_WithValue_SetsDelimiter()
    {
        // Arrange
        $delimiter = '|';
        
        // Act
        Config::setStringDelimiter($delimiter);
        $result = Config::getStringDelimiter();
        
        // Assert
        $this->assertEquals($delimiter, $result);
    }

    public function test_GetHashDelimiter_ByDefault_ReturnsDefault()
    {
        // Arrange & Act
        $result = Config::getHashDelimiter();
        
        // Assert
        $this->assertEquals('=', $result);
    }

    public function test_SetHashDelimiter_WithValue_SetsDelimiter()
    {
        // Arrange
        $delimiter = '|';
        
        // Act
        Config::setHashDelimiter($delimiter);
        $result = Config::getHashDelimiter();
        
        // Assert
        $this->assertEquals($delimiter, $result);
    }

    public function test_GetIdDelimiter_ReturnsStringDelimiter()
    {
        // Arrange
        Config::setStringDelimiter('|');
        
        // Act
        $result = Config::getIdDelimiter();
        
        // Assert
        $this->assertEquals('|', $result);
    }

    public function test_SetIdDelimiter_SetsStringDelimiter()
    {
        // Arrange
        $delimiter = '|';
        
        // Act
        Config::setIdDelimiter($delimiter);
        $result = Config::getStringDelimiter();
        
        // Assert
        $this->assertEquals($delimiter, $result);
    }

    public function test_GetStringDelimiter_FromEnvironment_ReturnsEnvValue()
    {
        // Arrange
        putenv('CACHE_ID_DELIMITER_STRING=test_delimiter');
        
        // Сбрасываем кэш через рефлексию
        $reflection = new \ReflectionClass(Config::class);
        $property = $reflection->getProperty('stringDelimiter');
        $property->setAccessible(true);
        $property->setValue(null, null);
        
        // Act
        $result = Config::getStringDelimiter();
        
        // Assert
        $this->assertEquals('test_delimiter', $result);
        
        // Cleanup
        putenv('CACHE_ID_DELIMITER_STRING');
    }

    public function test_GetHashDelimiter_FromEnvironment_ReturnsEnvValue()
    {
        // Arrange
        putenv('CACHE_ID_DELIMITER_HASH=test_hash_delimiter');
        
        // Сбрасываем кэш через рефлексию
        $reflection = new \ReflectionClass(Config::class);
        $property = $reflection->getProperty('hashDelimiter');
        $property->setAccessible(true);
        $property->setValue(null, null);
        
        // Act
        $result = Config::getHashDelimiter();
        
        // Assert
        $this->assertEquals('test_hash_delimiter', $result);
        
        // Cleanup
        putenv('CACHE_ID_DELIMITER_HASH');
    }
}

