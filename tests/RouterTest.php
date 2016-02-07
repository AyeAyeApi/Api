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
use AyeAye\Api\Tests\Injector\ControllerReflectorInjectorTest;

/**
 * Class RouterTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass AyeAye\Api\Router
 */
class RouterTest extends TestCase
{
    use ControllerReflectorInjectorTest;

    /**
     * @return Router
     */
    protected function getTestSubject()
    {
        return new Router();
    }

    /**
     * @test
     * @covers ::processRequest
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector
     */
    public function testProcessRequestNoRoute()
    {
        $method = 'get';
        $index = 'index';
        $documentation = $this->getMockControllerDocumentation();

        $controller = $this->getMockController();

        $request = $this->getMockRequest();
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->with()
            ->will($this->returnValue($method));
        $request
            ->expects($this->once())
            ->method('getRequestChain')
            ->with()
            ->will($this->returnValue([]));

        $reflectionController = $this->getMockReflectionController();

        $reflectionController
            ->expects($this->once())
            ->method('hasEndpoint')
            ->with($method, $index)
            ->will($this->returnValue(false));
        $reflectionController
            ->expects($this->once())
            ->method('getDocumentation')
            ->with()
            ->will($this->returnValue($documentation));

        $controllerReflector = $this->getMockControllerReflector();
        $controllerReflector
            ->expects($this->once())
            ->method('reflectController')
            ->with($controller)
            ->will($this->returnValue($reflectionController));

        $router = new Router();
        $router->setControllerReflector($controllerReflector);

        $this->assertSame(
            $documentation,
            $router->processRequest($request, $controller)
        );
    }

    /**
     * @test
     * @covers ::processRequest
     * @uses \AyeAye\Api\Router::setStatus
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector
     */
    public function testProcessRequestIndexOverride()
    {
        $method = 'post';
        $index = 'index';
        $requestChain = [];
        $data = new \stdClass();

        $status = $this->getMockStatus();

        $controller = $this->getMockController();

        $request = $this->getMockRequest();
        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->with()
            ->will($this->returnValue($method));

        $reflectionController = $this->getMockReflectionController();
        $reflectionController
            ->expects($this->never())
            ->method('hasChildController');
        $reflectionController
            ->expects($this->once())
            ->method('hasEndpoint')
            ->with($method, $index)
            ->will($this->returnValue(true));
        $reflectionController
            ->expects($this->once())
            ->method('getEndpointResult')
            ->with($method, $index, $request)
            ->will($this->returnValue($data));
        $reflectionController
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $controllerReflector = $this->getMockControllerReflector();
        $controllerReflector
            ->expects($this->once())
            ->method('reflectController')
            ->with($controller)
            ->will($this->returnValue($reflectionController));

        $router = new Router();
        $router->setControllerReflector($controllerReflector);

        $this->assertSame(
            $data,
            $router->processRequest($request, $controller, $requestChain)
        );
    }

    /**
     * @test
     * @covers ::processRequest
     * @uses \AyeAye\Api\Router::setStatus
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector
     */
    public function testProcessRequestSingleEndpoint()
    {
        $method = 'get';
        $endpointName = 'test';
        $requestChain = [$endpointName];
        $data = new \stdClass();

        $status = $this->getMockStatus();

        $controller = $this->getMockController();

        $request = $this->getMockRequest();
        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->with()
            ->will($this->returnValue($method));

        $reflectionController = $this->getMockReflectionController();
        $reflectionController
            ->expects($this->once())
            ->method('hasChildController')
            ->with($endpointName)
            ->will($this->returnValue(false));
        $reflectionController
            ->expects($this->once())
            ->method('hasEndpoint')
            ->with($method, $endpointName)
            ->will($this->returnValue(true));
        $reflectionController
            ->expects($this->once())
            ->method('getEndpointResult')
            ->with($method, $endpointName, $request)
            ->will($this->returnValue($data));
        $reflectionController
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $controllerReflector = $this->getMockControllerReflector();
        $controllerReflector
            ->expects($this->once())
            ->method('reflectController')
            ->with($controller)
            ->will($this->returnValue($reflectionController));

        $router = new Router();
        $router->setControllerReflector($controllerReflector);

        $this->assertSame(
            $data,
            $router->processRequest($request, $controller, $requestChain)
        );
    }

    /**
     * @test
     * @covers ::processRequest
     * @uses \AyeAye\Api\Router::setStatus
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector
     */
    public function testProcessRequestControllerRoutedEndpoint()
    {
        $method = 'get';
        $controller1 = 'controller-one';
        $controller2 = 'controller-two';
        $endpointName = 'test';
        $requestChain = [$controller1, $controller2, $endpointName];
        $data = new \stdClass();

        $status = $this->getMockStatus();

        $controller = $this->getMockController();

        $request = $this->getMockRequest();
        $request
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->with()
            ->will($this->returnValue($method));

        $reflectionController = $this->getMockReflectionController();
        $reflectionController
            ->expects($this->at(0))
            ->method('hasChildController')
            ->with($controller1)
            ->will($this->returnValue(true));
        $reflectionController
            ->expects($this->at(1))
            ->method('getChildController')
            ->with($controller1)
            ->will($this->returnValue($controller));
        $reflectionController
            ->expects($this->at(2))
            ->method('hasChildController')
            ->with($controller2)
            ->will($this->returnValue(true));
        $reflectionController
            ->expects($this->at(3))
            ->method('getChildController')
            ->with($controller2)
            ->will($this->returnValue($controller));
        $reflectionController
            ->expects($this->at(4))
            ->method('hasChildController')
            ->with($endpointName)
            ->will($this->returnValue(false));
        $reflectionController
            ->expects($this->once())
            ->method('hasEndpoint')
            ->with($method, $endpointName)
            ->will($this->returnValue(true));
        $reflectionController
            ->expects($this->once())
            ->method('getEndpointResult')
            ->with($method, $endpointName, $request)
            ->will($this->returnValue($data));
        $reflectionController
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $controllerReflector = $this->getMockControllerReflector();
        $controllerReflector
            ->expects($this->any())
            ->method('reflectController')
            ->with($controller)
            ->will($this->returnValue($reflectionController));

        $router = new Router();
        $router->setControllerReflector($controllerReflector);

        $this->assertSame(
            $data,
            $router->processRequest($request, $controller, $requestChain)
        );
    }

    /**
     * @test
     * @covers ::processRequest
     * @uses \AyeAye\Api\Router::setStatus
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 404
     * @expectedExceptionMessage Could not find controller or endpoint matching 'test'
     */
    public function testProcessRequestUnknownEndpoint()
    {
        $method = 'get';
        $endpointName = 'test';
        $requestChain = [$endpointName];
        $data = new \stdClass();

        $controller = $this->getMockController();

        $request = $this->getMockRequest();
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->with()
            ->will($this->returnValue($method));

        $reflectionController = $this->getMockReflectionController();
        $reflectionController
            ->expects($this->once())
            ->method('hasChildController')
            ->with($endpointName)
            ->will($this->returnValue(false));
        $reflectionController
            ->expects($this->once())
            ->method('hasEndpoint')
            ->with($method, $endpointName)
            ->will($this->returnValue(false));

        $controllerReflector = $this->getMockControllerReflector();
        $controllerReflector
            ->expects($this->once())
            ->method('reflectController')
            ->with($controller)
            ->will($this->returnValue($reflectionController));

        $router = new Router();
        $router->setControllerReflector($controllerReflector);

        $this->assertSame(
            $data,
            $router->processRequest($request, $controller, $requestChain)
        );
    }
}
