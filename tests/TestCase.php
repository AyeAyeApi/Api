<?php
/**
 * TestCase.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\ControllerDocumentation;
use AyeAye\Api\ControllerReflector;
use AyeAye\Api\Documentation;
use AyeAye\Api\Exception as AyeAyeException;
use AyeAye\Api\Exception;
use AyeAye\Api\ReflectionController;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Router;
use AyeAye\Formatter\Writer;
use AyeAye\Formatter\WriterFactory;
use AyeAye\Api\Status;
use Psr\Log\AbstractLogger;

/**
 * Class TestCase
 * Abstract Testing class to provide additional utilities
 * @abstract
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an otherwise inaccessible method
     * @param object $object
     * @param $methodName
     * @return callable
     */
    protected function getObjectMethod($object, $methodName)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Can not get method of non object');
        }
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        $reflectionMethod->setAccessible(true);
        return function () use ($object, $reflectionMethod) {
            return $reflectionMethod->invokeArgs($object, func_get_args());
        };
    }

    /**
     * @param object $object        The object to update
     * @param string $attributeName The attribute to change
     * @param mixed  $value         The value to change it to
     */
    protected function setObjectAttribute($object, $attributeName, $value)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($attributeName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * @return \ReflectionMethod|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockReflectionMethod()
    {
        $mockReflection = $this
            ->getMockBuilder(\ReflectionMethod::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $mockReflection;
    }

    /**
     * @return \ReflectionObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockReflectionObject()
    {
        $mockReflection = $this
            ->getMockBuilder(\ReflectionObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $mockReflection;
    }

    /**
     * @return \ReflectionObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockReflectionParameter()
    {
        $mockReflection = $this
            ->getMockBuilder(\ReflectionParameter::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $mockReflection;
    }

    /**
     * @return Controller|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockController()
    {
        return $this->getMock(Controller::class);
    }

    protected function getMockControllerDocumentation()
    {
        return $this
            ->getMockBuilder(ControllerDocumentation::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ControllerReflector|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockControllerReflector()
    {
        return $this->getMock(ControllerReflector::class);
    }

    /**
     * @return ReflectionController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockReflectionController()
    {
        return $this
            ->getMockBuilder(ReflectionController::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Documentation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDocumentation()
    {
        return $this->getMock(Documentation::class);
    }

    /**
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRequest()
    {
        return $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockResponse()
    {
        return $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRouter()
    {
        return $this->getMock(Router::class);
    }

    /**
     * @return Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockStatus()
    {
        return $this
            ->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return WriterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockWriterFactory()
    {
        return $this
            ->getMockBuilder(WriterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockWriter()
    {
        return $this->getMock(Writer::class);
    }

    /**
     * @return AbstractLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLogger()
    {
        return $this->getMockForAbstractClass(AbstractLogger::class);
    }

    /**
     * @return AyeAyeException|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAyeAyeException()
    {
        return $this
            ->getMockBuilder(Exception::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
