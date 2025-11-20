<?php

namespace pribolshoy\repository\tests\utils;

use pribolshoy\repository\Logger;
use pribolshoy\repository\tests\CommonTestCase;

class LoggerTest extends CommonTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Logger::clear();
        Logger::enable();
    }

    protected function tearDown(): void
    {
        Logger::clear();
        Logger::enable();
        parent::tearDown();
    }

    public function test_Log_WithOperationAndKey_AddsLog()
    {
        // Arrange
        $operation = 'get';
        $key = 'test_key';
        $category = 'cache';
        
        // Act
        Logger::log($operation, $key, $category);
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals($operation, $logs[0]['operation']);
        $this->assertEquals($key, $logs[0]['key']);
        $this->assertEquals($category, $logs[0]['category']);
    }

    public function test_Log_WithResult_AddsCount()
    {
        // Arrange
        $operation = 'get';
        $key = 'test_key';
        $result = ['item1', 'item2', 'item3'];
        
        // Act
        Logger::log($operation, $key, 'cache', $result);
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals(3, $logs[0]['count']);
    }

    public function test_Log_WhenDisabled_DoesNotAddLog()
    {
        // Arrange
        Logger::disable();
        
        // Act
        Logger::log('get', 'test_key');
        
        // Assert
        $this->assertCount(0, Logger::getLogs());
    }

    public function test_GetLogs_ReturnsAllLogs()
    {
        // Arrange
        Logger::log('get', 'key1');
        Logger::log('set', 'key2');
        Logger::log('delete', 'key3');
        
        // Act
        $logs = Logger::getLogs();
        
        // Assert
        $this->assertCount(3, $logs);
    }

    public function test_GetLogsByOperation_ReturnsFilteredLogs()
    {
        // Arrange
        Logger::log('get', 'key1');
        Logger::log('set', 'key2');
        Logger::log('get', 'key3');
        
        // Act
        $logs = Logger::getLogsByOperation('get');
        
        // Assert
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals('get', $log['operation']);
        }
    }

    public function test_GetLogsByCategory_ReturnsFilteredLogs()
    {
        // Arrange
        Logger::log('get', 'key1', 'cache');
        Logger::log('set', 'key2', 'repository');
        Logger::log('get', 'key3', 'cache');
        
        // Act
        $logs = Logger::getLogsByCategory('cache');
        
        // Assert
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals('cache', $log['category']);
        }
    }

    public function test_Clear_RemovesAllLogs()
    {
        // Arrange
        Logger::log('get', 'key1');
        Logger::log('set', 'key2');
        
        // Act
        Logger::clear();
        
        // Assert
        $this->assertCount(0, Logger::getLogs());
    }

    public function test_Count_ReturnsNumberOfLogs()
    {
        // Arrange
        Logger::log('get', 'key1');
        Logger::log('set', 'key2');
        Logger::log('delete', 'key3');
        
        // Act
        $count = Logger::count();
        
        // Assert
        $this->assertEquals(3, $count);
    }

    public function test_Enable_EnablesLogging()
    {
        // Arrange
        Logger::disable();
        
        // Act
        Logger::enable();
        Logger::log('get', 'test_key');
        
        // Assert
        $this->assertCount(1, Logger::getLogs());
    }

    public function test_Disable_DisablesLogging()
    {
        // Arrange
        Logger::enable();
        
        // Act
        Logger::disable();
        Logger::log('get', 'test_key');
        
        // Assert
        $this->assertCount(0, Logger::getLogs());
    }

    public function test_IsEnabled_ReturnsStatus()
    {
        // Arrange & Act
        Logger::enable();
        $enabled = Logger::isEnabled();
        
        // Assert
        $this->assertTrue($enabled);
        
        // Act
        Logger::disable();
        $disabled = Logger::isEnabled();
        
        // Assert
        $this->assertFalse($disabled);
    }

    public function test_SetMaxLogs_LimitsLogCount()
    {
        // Arrange
        Logger::setMaxLogs(2);
        
        // Act
        Logger::log('get', 'key1');
        Logger::log('get', 'key2');
        Logger::log('get', 'key3');
        
        // Assert
        $this->assertCount(2, Logger::getLogs());
        $this->assertEquals('key2', Logger::getLogs()[0]['key']);
        $this->assertEquals('key3', Logger::getLogs()[1]['key']);
    }

    public function test_AddCategory_AddsNewCategory()
    {
        // Arrange & Act
        Logger::addCategory('custom');
        $categories = Logger::getCategories();
        
        // Assert
        $this->assertContains('custom', $categories);
    }

    public function test_Log_WithInvalidCategory_UsesDefault()
    {
        // Arrange & Act
        Logger::log('get', 'test_key', 'invalid_category');
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertEquals('default', $logs[0]['category']);
    }

    public function test_GetCount_WithArray_ReturnsCount()
    {
        // Arrange
        $result = ['item1', 'item2', 'item3'];
        
        // Act
        Logger::log('get', 'key', 'cache', $result);
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertEquals(3, $logs[0]['count']);
    }

    public function test_GetCount_WithObject_ReturnsOne()
    {
        // Arrange
        $result = new \stdClass();
        
        // Act
        Logger::log('get', 'key', 'cache', $result);
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertEquals(1, $logs[0]['count']);
    }

    public function test_GetCount_WithNull_ReturnsZero()
    {
        // Arrange & Act
        Logger::log('get', 'key', 'cache', null);
        
        // Assert
        $logs = Logger::getLogs();
        $this->assertArrayNotHasKey('count', $logs[0]);
    }
}

