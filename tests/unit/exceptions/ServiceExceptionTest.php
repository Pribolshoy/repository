<?php

namespace pribolshoy\repository\tests\exceptions;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\tests\CommonTestCase;

class ServiceExceptionTest extends CommonTestCase
{
    public function test_CreateException_WithMessage_ReturnsException()
    {
        // Arrange
        $message = 'Test service exception message';
        
        // Act
        $exception = new ServiceException($message);
        
        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(ServiceException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function test_CreateException_WithMessageAndCode_ReturnsException()
    {
        // Arrange
        $message = 'Test service exception message';
        $code = 400;
        
        // Act
        $exception = new ServiceException($message, $code);
        
        // Assert
        $this->assertInstanceOf(ServiceException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function test_CreateException_WithPreviousException_ReturnsException()
    {
        // Arrange
        $message = 'Test service exception message';
        $previousException = new \Exception('Previous exception');
        
        // Act
        $exception = new ServiceException($message, 0, $previousException);
        
        // Assert
        $this->assertInstanceOf(ServiceException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function test_Exception_CanBeThrown()
    {
        // Arrange
        $message = 'Test service exception message';
        
        // Assert
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage($message);
        
        // Act
        throw new ServiceException($message);
    }

    public function test_Exception_CanBeCaught()
    {
        // Arrange
        $message = 'Test service exception message';
        $caught = false;
        
        // Act
        try {
            throw new ServiceException($message);
        } catch (ServiceException $e) {
            $caught = true;
            $this->assertEquals($message, $e->getMessage());
        }
        
        // Assert
        $this->assertTrue($caught);
    }
}

