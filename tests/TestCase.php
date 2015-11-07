<?php
/**
 * ExceptionTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

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
}
