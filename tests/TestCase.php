<?php
/**
 * Abstract Testing class to provide additional utilities
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an otherwise inaccessible method
     * @param $class
     * @param $methodName
     * @return \ReflectionMethod
     */
    protected function getClassMethod($class, $methodName)
    {
        $method = new \ReflectionMethod($class, $methodName);
        $method->setAccessible(true);
        return $method;
    }
}
