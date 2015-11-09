<?php
/**
 * ExceptionTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\Exception as AyeAyeException;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Router;
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
        $method = new \ReflectionMethod($object, $methodName);
        $method->setAccessible(true);
        $callable = function () use ($object, $method) {
            $arguments = func_get_args();
            array_unshift($arguments, $object);
            return call_user_func_array([$method, 'invoke'], $arguments);
        };
        return $callable;
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
     * @return Controller|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockController()
    {
        return $this->getMock('\AyeAye\Api\Controller');
    }

    /**
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRequest()
    {
        return $this
            ->getMockBuilder('\AyeAye\Api\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockResponse()
    {
        return $this
            ->getMockBuilder('\AyeAye\Api\Response')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRouter()
    {
        return $this->getMock('\AyeAye\Api\Router');
    }

    /**
     * @return Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockStatus()
    {
        return $this
            ->getMockBuilder('\AyeAye\Api\Status')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return WriterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockWriterFactory()
    {
        return $this
            ->getMockBuilder('\AyeAye\Formatter\WriterFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return AbstractLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLogger()
    {
        return $this->getMockForAbstractClass('\Psr\Log\AbstractLogger');
    }

    /**
     * @return AyeAyeException|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAyeAyeException()
    {
        return $this
            ->getMockBuilder('\AyeAye\Api\Exception')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
