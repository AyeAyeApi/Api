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
            $controller->isControllerHiddenMethod('hiddenChildController')
        );
        $this->assertFalse(
            $controller->isControllerHiddenMethod('childController')
        );

        $hideControllerMethod = $this->getClassMethod($controller, 'hideControllerMethod');
        $hideControllerMethod->invoke($controller, 'childController');
        $showControllerMethod = $this->getClassMethod($controller, 'showControllerMethod');
        $showControllerMethod->invoke($controller, 'hiddenChildController');

        $this->assertTrue(
            $controller->isControllerHiddenMethod('childController')
        );
        $this->assertFalse(
            $controller->isControllerHiddenMethod('hiddenChildController')
        );
    }

    public function testHiddenControllers()
    {
        $controller = new TestController();
        $this->assertTrue(
            $controller->isEndpointMethodHidden('getHiddenEndpoint')
        );
        $this->assertFalse(
            $controller->isEndpointMethodHidden('getInformationEndpoint')
        );

        $hideEndpointMethod = $this->getClassMethod($controller, 'hideEndpointMethod');
        $hideEndpointMethod->invoke($controller, 'getInformationEndpoint');
        $showEndpointMethod = $this->getClassMethod($controller, 'showEndpointMethod');
        $showEndpointMethod->invoke($controller, 'getHiddenEndpoint');

        $this->assertTrue(
            $controller->isEndpointMethodHidden('getInformationEndpoint')
        );
        $this->assertFalse(
            $controller->isEndpointMethodHidden('getHiddenEndpoint')
        );
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testHideControllerException()
    {
        $controller = new TestController();
        $hideControllerMethod = $this->getClassMethod($controller, 'hideControllerMethod');
        $hideControllerMethod->invoke($controller, 'noSuchController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testHideEndpointException()
    {
        $controller = new TestController();
        $hideEndpointMethod = $this->getClassMethod($controller, 'hideEndpointMethod');
        $hideEndpointMethod->invoke($controller, 'noSuchEndpoint');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testIsControllerHiddenException()
    {
        $controller = new TestController();
        $controller->isControllerHiddenMethod('noSuchController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testIsEndpointHiddenException()
    {
        $controller = new TestController();
        $controller->isEndpointMethodHidden('noSuchEndpoint');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchController' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testShowControllerException()
    {
        $controller = new TestController();
        $showControllerMethod = $this->getClassMethod($controller, 'showControllerMethod');
        $showControllerMethod->invoke($controller, 'noSuchController');
    }

    /**
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionMessage The method 'noSuchEndpoint' does not exist in AyeAye\Api\Tests\TestData\TestController
     * @expectedExceptionCode    500
     */
    public function testShowEndpointException()
    {
        $controller = new TestController();
        $showEndpointMethod = $this->getClassMethod($controller, 'showEndpointMethod');
        $showEndpointMethod->invoke($controller, 'noSuchEndpoint');
    }
}
