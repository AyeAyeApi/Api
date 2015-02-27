<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\Exception;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\TestBrokenController;
use AyeAye\Api\Tests\TestData\TestController;

/**
 * Test for the Controller Class
 * @package AyeAye\Api\Tests
 */
class ControllerTest extends TestCase
{

    public function testStatusCode()
    {
        $router = new Controller();
        $status = $router->getStatus();

        $this->assertSame(
            200,
            $status->getCode()
        );

        $this->assertSame(
            'OK',
            $status->getMessage()
        );

        $setStatus = $this->getClassMethod($router, 'setStatus');
        $setStatus->invoke($router, new Status(500));
        $status = $router->getStatus();

        $this->assertSame(
            500,
            $status->getCode()
        );

        $this->assertSame(
            'Internal Server Error',
            $status->getMessage()
        );

        $router = new TestController();
        $setStatusCode = $this->getClassMethod($router, 'setStatusCode');
        $setStatusCode->invoke($router, 418);
        $status = $router->getStatus();

        $this->assertSame(
            418,
            $status->getCode()
        );

        $this->assertSame(
            'I\'m a teapot',
            $status->getMessage()
        );
    }

    public function testHiddenEndpoints()
    {
        $controller = new TestController();
        $this->assertTrue(
            $controller->isMethodHidden('hiddenChildController')
        );
        $this->assertFalse(
            $controller->isMethodHidden('childController')
        );

        $hideControllerMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideControllerMethod->invoke($controller, 'childController');
        $showControllerMethod = $this->getClassMethod($controller, 'showMethod');
        $showControllerMethod->invoke($controller, 'hiddenChildController');

        $this->assertTrue(
            $controller->isMethodHidden('childController')
        );
        $this->assertFalse(
            $controller->isMethodHidden('hiddenChildController')
        );

        $controller = new TestBrokenController();
        $this->assertFalse(
            $controller->isMethodHidden('childController')
        );
        $hideControllerMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideControllerMethod->invoke($controller, 'childController');
        $this->assertTrue(
            $controller->isMethodHidden('childController')
        );
    }

    public function testHiddenControllers()
    {
        $controller = new TestController();
        $this->assertTrue(
            $controller->isMethodHidden('getHiddenEndpoint')
        );
        $this->assertFalse(
            $controller->isMethodHidden('getInformationEndpoint')
        );

        $hideEndpointMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideEndpointMethod->invoke($controller, 'getInformationEndpoint');
        $showEndpointMethod = $this->getClassMethod($controller, 'showMethod');
        $showEndpointMethod->invoke($controller, 'getHiddenEndpoint');

        $this->assertTrue(
            $controller->isMethodHidden('getInformationEndpoint')
        );
        $this->assertFalse(
            $controller->isMethodHidden('getHiddenEndpoint')
        );

        $controller = new TestBrokenController();
        $this->assertFalse(
            $controller->isMethodHidden('getInformationEndpoint')
        );

        $hideEndpointMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideEndpointMethod->invoke($controller, 'getInformationEndpoint');

        $this->assertTrue(
            $controller->isMethodHidden('getInformationEndpoint')
        );
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testHideControllerException()
    {
        $controller = new TestController();
        $hideControllerMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideControllerMethod->invoke($controller, 'fakeController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testHideEndpointException()
    {
        $controller = new TestController();
        $hideEndpointMethod = $this->getClassMethod($controller, 'hideMethod');
        $hideEndpointMethod->invoke($controller, 'fakeEndpoint');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testIsControllerHiddenException()
    {
        $controller = new TestController();
        $controller->isMethodHidden('fakeController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testIsEndpointHiddenException()
    {
        $controller = new TestController();
        $controller->isMethodHidden('fakeEndpoint');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testShowControllerException()
    {
        $controller = new TestController();
        $showControllerMethod = $this->getClassMethod($controller, 'showMethod');
        $showControllerMethod->invoke($controller, 'fakeController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'fakeEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testShowEndpointException()
    {
        $controller = new TestController();
        $showEndpointMethod = $this->getClassMethod($controller, 'showMethod');
        $showEndpointMethod->invoke($controller, 'fakeEndpoint');
    }
}
