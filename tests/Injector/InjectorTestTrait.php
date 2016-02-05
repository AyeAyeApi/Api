<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 23:52
 */

namespace AyeAye\Api\Tests\Injector;


use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Router;
use AyeAye\Formatter\WriterFactory;
use Psr\Log\LoggerInterface;

trait InjectorTestTrait
{
    /**
     * @return mixed
     */
    abstract protected function getTestSubject();

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockLogger();

    /**
     * @return Request|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockRequest();

    /**
     * @return Response|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockResponse();

    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockRouter();

    /**
     * @return WriterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockWriterFactory();


    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    abstract public function assertSame($expected, $actual, $message = '');

    /**
     * Asserts that a variable is null.
     *
     * @param mixed  $actual
     * @param string $message
     */
    abstract public function assertNull($actual, $message = '');

    /**
     * Returns the value of an object's attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param object $object
     * @param string $attributeName
     *
     * @return mixed
     *
     * @throws \PHPUnit_Framework_Exception
     *
     * @since  Method available since Release 4.0.0
     */
    abstract public function getObjectAttribute($object, $attributeName);

    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    abstract public function assertNotSame($expected, $actual, $message = '');

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     *
     * @since Method available since Release 3.5.0
     */
    abstract public function assertInstanceOf($expected, $actual, $message = '');
}
