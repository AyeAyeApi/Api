<?php
/**
 * RouterTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Router;
use AyeAye\Api\Status;

/**
 * Class RouterTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass AyeAye\Api\Router
 */
class RouterTest extends TestCase
{

    /**
     * @test
     * @covers ::camelcaseToHyphenated
     */
    public function testCamelcaseToHyphenated()
    {
        $router = new Router();
        $camelcaseToHyphenated = $this->getObjectMethod($router, 'camelcaseToHyphenated');

        $this->assertSame(
            'camelcase-to-hyphenated',
            $camelcaseToHyphenated('camelcaseToHyphenated')
        );
    }

    /**
     * @test
     * @covers ::parseEndpointName
     */
    public function testParseEndpointName()
    {
        $router = new Router();
        $parseEndpointName = $this->getObjectMethod($router, 'parseEndpointName');

        $this->assertSame(
            'getTestEndpoint',
            $parseEndpointName('test')
        );

        $this->assertSame(
            'putCamelCaseEndpoint',
            $parseEndpointName('camel-case', 'put')
        );

        $this->assertSame(
            'postCamelCaseEndpoint',
            $parseEndpointName('camel+case', 'POST')
        );

        $this->assertSame(
            'optionsCamelCaseEndpoint',
            $parseEndpointName('camel%20case', 'oPtIoNs')
        );
    }

    /**
     * @test
     * @covers ::parseControllerName
     */
    public function testParseControllerName()
    {
        $router = new Router();
        $parseControllerName = $this->getObjectMethod($router, 'parseControllerName');

        // ToDo: Should this be an error?
        $this->assertSame(
            'Controller',
            $parseControllerName('')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel-case')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel%20case')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel+case')
        );
    }

//    /**
//     * @test
//     * @covers ::getControllers
//     */
//    public function testGetControllers()
//    {
//        $mockController = $this->getMockForAbstractClass('AyeAye\Api\Controller');
//        $mockController->method('testController');
//        $mockController->method('anotherTestController');
//
//        $router = new Router();
//        $getControllers = $this->getObjectMethod($router, 'getControllers');
//
//        $this->assertCount(
//            2,
//            $getControllers($mockController)
//        );
//    }

    /**
     * @test
     * @covers ::getStatus
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Router::setStatus
     */
    public function testGetStatus()
    {
        $router = new Router();
        $status = $this->getMockStatus();

        $this->assertInstanceOf(
            'AyeAye\Api\Status',
            $router->getStatus()
        );
        $this->assertNotSame(
            $status,
            $router->getStatus()
        );

        $setStatus = $this->getObjectMethod($router, 'setStatus');
        $setStatus($status);

        $this->assertInstanceOf(
            'AyeAye\Api\Status',
            $router->getStatus()
        );
        $this->assertSame(
            $status,
            $router->getStatus()
        );
    }

    /**
     * @test
     * @covers ::setStatus
     * @uses AyeAye\Api\Router::getStatus
     */
    public function testSetStatus()
    {
        $status = $this->getMockStatus();
        $router = new Router();

        $setStatus = $this->getObjectMethod($router, 'setStatus');
        $setStatus($status);
        $this->assertSame(
            $status,
            $router->getStatus()
        );
    }
}
