<?php

namespace pribolshoy\repository\tests\exceptions;

use pribolshoy\repository\exceptions\RepositoryException;
use pribolshoy\repository\tests\CommonTestCase;

class RepositoryExceptionTest extends CommonTestCase
{
    public function test_CreateException_WithMessage_ReturnsException()
    {
        // Arrange
        $message = 'Test repository exception message';
        
        // Act
        $exception = new RepositoryException($message);
        
        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function test_CreateException_WithMessageAndCode_ReturnsException()
    {
        // Arrange
        $message = 'Test repository exception message';
        $code = 500;
        
        // Act
        $exception = new RepositoryException($message, $code);
        
        // Assert
        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function test_CreateException_WithPreviousException_ReturnsException()
    {
        // Arrange
        $message = 'Test repository exception message';
        $previousException = new \Exception('Previous exception');
        
        // Act
        $exception = new RepositoryException($message, 0, $previousException);
        
        // Assert
        $this->assertInstanceOf(RepositoryException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function test_Exception_CanBeThrown()
    {
        // Arrange
        $message = 'Test repository exception message';
        
        // Assert
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage($message);
        
        // Act
        throw new RepositoryException($message);
    }

    public function test_Exception_CanBeCaught()
    {
        // Arrange
        $message = 'Test repository exception message';
        $caught = false;
        
        // Act
        try {
            throw new RepositoryException($message);
        } catch (RepositoryException $e) {
            $caught = true;
            $this->assertEquals($message, $e->getMessage());
        }
        
        // Assert
        $this->assertTrue($caught);
    }
}

