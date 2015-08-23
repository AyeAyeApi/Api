<?php
/**
 * Abstract Testing class to provide additional utilities
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

/**
 * Class TestCase
 * @abstract
 * @package AyeAye\Api\Tests
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
}
