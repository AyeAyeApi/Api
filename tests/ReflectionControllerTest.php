<?php
/**
 * ReflectionControllerTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\ControllerDocumentation;
use AyeAye\Api\ReflectionController;
use AyeAye\Formatter\Deserializable;

/**
 * Class ReflectionControllerTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\ReflectionController
 */
class ReflectionControllerTest extends TestCase
{

    /**
     * @test
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $controller = $this->getMockController();
        $reflectionController = new ReflectionController($controller);

        $this->assertSame(
            $controller,
            $this->getObjectAttribute($reflectionController, 'controller')
        );
        $this->assertInstanceOf(
            \ReflectionObject::class,
            $this->getObjectAttribute($reflectionController, 'reflection')
        );
    }

    /**
     * @test
     * @covers ::getDocumentation
     * @uses \AyeAye\Api\ControllerDocumentation
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testDocumentController()
    {
        $className = 'ControllerTest';
        /** @var Controller|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this
            ->getMockBuilder(Controller::class)
            ->setMockClassName($className)
            ->getMock();
        $reflectionController = new ReflectionController($controller);

        $documentController = $this->getObjectMethod($reflectionController, 'getDocumentation');

        $getDocumentation = $documentController($controller);

        $this->assertInstanceOf(
            ControllerDocumentation::class,
            $getDocumentation
        );

        $this->assertSame(
            $className,
            $this->getObjectAttribute($getDocumentation, 'reflectedController')->getName()
        );
    }

    /**
     * @test
     * @covers ::hasEndpoint
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseEndpointName
     */
    public function testHasEndpoint()
    {
        $controller = $this->getMockController();
        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('getTestEndpoint')
            ->will($this->returnValue(true));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $this->assertTrue(
            $reflectionController->hasEndpoint('get', 'test')
        );
    }

    /**
     * @test
     * @covers ::getEndpointResult
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseEndpointName
     * @uses \AyeAye\Api\ReflectionController::mapRequestToArguments
     */
    public function testGetEndpointResult()
    {
        $request = $this->getMockRequest();
        $controller = $this->getMockController();

        $reflectionMethod =
            $this
                ->getMockBuilder(\ReflectionMethod::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionMethod
            ->expects($this->once())
            ->method('invokeArgs')
            ->with($controller, [])
            ->will($this->returnValue(true));
        $reflectionMethod
            ->expects($this->once())
            ->method('getParameters')
            ->with()
            ->will($this->returnValue([]));

        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('getTestEndpoint')
            ->will($this->returnValue(true));
        $reflectionOverride
            ->expects($this->once())
            ->method('getMethod')
            ->with('getTestEndpoint')
            ->will($this->returnValue($reflectionMethod));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $this->assertTrue(
            $reflectionController->getEndpointResult('get', 'test', $request)
        );
    }

    /**
     * @test
     * @covers ::getEndpointResult
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseEndpointName
     * @expectedException \RuntimeException
     * @expectedExceptionMessage TestController::getTestEndpoint does not exist
     */
    public function testGetEndpointResultException()
    {
        $controllerName = 'TestController';
        $request = $this->getMockRequest();
        $controller = $this->getMockController();

        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('getTestEndpoint')
            ->will($this->returnValue(false));
        $reflectionOverride
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($controllerName));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $reflectionController->getEndpointResult('get', 'test', $request);

    }

    /**
     * @test
     * @covers ::parseEndpointName
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testParseEndpointName()
    {
        $controller = $this->getMockController();
        $reflectionController = new ReflectionController($controller);

        $parseEndpointName = $this->getObjectMethod($reflectionController, 'parseEndpointName');


        $this->assertSame(
            'putCamelCaseEndpoint',
            $parseEndpointName('put', 'camel-case')
        );

        $this->assertSame(
            'postCamelCaseEndpoint',
            $parseEndpointName('POST', 'camel+case')
        );

        $this->assertSame(
            'optionsCamelCaseEndpoint',
            $parseEndpointName('oPtIoNs', 'camel%20case')
        );
    }

    /**
     * @test
     * @covers ::hasChildController
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseControllerName
     */
    public function testHasChildController()
    {
        /** @var Controller|\PHPUnit_Framework_MockObject_MockObject $controller */
        $controller = $this->getMockController();
        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('childController')
            ->will($this->returnValue(true));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $this->assertTrue(
            $reflectionController->hasChildController('child')
        );
    }

    /**
     * @test
     * @covers ::getChildController
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseControllerName
     * @uses \AyeAye\Api\ReflectionController::hasChildController
     */
    public function testGetChildController()
    {
        $controller = $this->getMockController();
        $childController = $this->getMockController();
        $controllerMethod =
            $this
                ->getMockBuilder(\ReflectionMethod::class)
                ->disableOriginalConstructor()
                ->getMock();
        $controllerMethod
            ->expects($this->once())
            ->method('invokeArgs')
            ->with($controller)
            ->will($this->returnValue($childController));
        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('childController')
            ->will($this->returnValue(true));
        $reflectionOverride
            ->expects($this->once())
            ->method('getMethod')
            ->with('childController')
            ->will($this->returnValue($controllerMethod));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $this->assertSame(
            $childController,
            $reflectionController->getChildController('child')
        );
    }

    /**
     * @test
     * @covers ::getChildController
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseControllerName
     * @uses \AyeAye\Api\ReflectionController::hasChildController
     * @expectedException \RuntimeException
     * @expectedExceptionMessage TestController::childController does not exist
     */
    public function testGetChildControllerNoMethod()
    {
        $controller = $this->getMockController();
        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('childController')
            ->will($this->returnValue(false));
        $reflectionOverride
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue('TestController'));
        $reflectionOverride
            ->expects($this->never())
            ->method('getMethod');

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $reflectionController->getChildController('child');
    }

    /**
     * @test
     * @covers ::getChildController
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @uses \AyeAye\Api\ReflectionController::parseControllerName
     * @uses \AyeAye\Api\ReflectionController::hasChildController
     * @expectedException \RuntimeException
     * @expectedExceptionMessage TestController::childController did not return a Controller
     */
    public function testGetChildControllerNotController()
    {
        $controller = $this->getMockController();
        $childController = $this->getMock(\stdClass::class);
        $controllerMethod =
            $this
                ->getMockBuilder(\ReflectionMethod::class)
                ->disableOriginalConstructor()
                ->getMock();
        $controllerMethod
            ->expects($this->once())
            ->method('invokeArgs')
            ->with($controller)
            ->will($this->returnValue($childController));
        $reflectionOverride =
            $this
                ->getMockBuilder(\ReflectionObject::class)
                ->disableOriginalConstructor()
                ->getMock();
        $reflectionOverride
            ->expects($this->once())
            ->method('hasMethod')
            ->with('childController')
            ->will($this->returnValue(true));
        $reflectionOverride
            ->expects($this->once())
            ->method('getMethod')
            ->with('childController')
            ->will($this->returnValue($controllerMethod));
        $reflectionOverride
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue('TestController'));

        $reflectionController = new ReflectionController($controller);
        $this->setObjectAttribute($reflectionController, 'reflection', $reflectionOverride);

        $reflectionController->getChildController('child');
    }

    /**
     * @test
     * @covers ::parseControllerName
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testParseControllerName()
    {
        $controller = $this->getMockController();
        $reflectionController = new ReflectionController($controller);

        $parseControllerName = $this->getObjectMethod($reflectionController, 'parseControllerName');

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

    /**
     * @test
     * @covers ::mapRequestToArguments
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testMapRequestToArguments()
    {
        $controller = $this->getMockController();
        $reflectionController = new ReflectionController($controller);

        $name1 = 'param1';
        $expectedValue1 = null;
        $parameter1 = $this->getMockReflectionParameter();
        $parameter1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name1));
        $parameter1
            ->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->will($this->returnValue(false));
        $parameter1
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(null));

        $name2 = 'param2';
        $expectedValue2 = true;
        $parameter2 = $this->getMockReflectionParameter();
        $parameter2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name2));
        $parameter2
            ->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->will($this->returnValue(true));
        $parameter2
            ->expects($this->any())
            ->method('getDefaultValue')
            ->will($this->returnValue($expectedValue2));
        $parameter2
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue(null));

        $inputValue3 = 'input value 3';
        $object3 = $this->getMockForAbstractClass(Deserializable::class);
        $object3
            ->expects($this->once())
            ->method('ayeAyeDeserialize')
            ->with($inputValue3)
            ->will($this->returnValue($object3));
        $class3 = $this->getMockReflectionObject();
        $class3
            ->expects($this->once())
            ->method('implementsInterface')
            ->with(Deserializable::class)
            ->will($this->returnValue(true));
        $class3
            ->expects($this->once())
            ->method('newInstanceWithoutConstructor')
            ->with()
            ->will($this->returnValue($object3));
        $class3
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue(get_class($object3)));
        $name3 = 'param3';
        $expectedValue3 = $object3;
        $parameter3 = $this->getMockReflectionParameter();
        $parameter3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name3));
        $parameter3
            ->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->will($this->returnValue(false));
        $parameter3
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue($class3));

        $request = $this->getMockRequest();
        $request
            ->expects($this->at(0))
            ->method('getParameter')
            ->with($name1, null)
            ->will($this->returnValue(null));
        $request
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($name2, $expectedValue2)
            ->will($this->returnValue($expectedValue2));
        $request
            ->expects($this->at(2))
            ->method('getParameter')
            ->with($name3, null)
            ->will($this->returnValue($inputValue3));

        $method = $this->getMockReflectionMethod();
        $method
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue([
                $parameter1,
                $parameter2,
                $parameter3,
            ]));

        $mapRequestToArguments = $this->getObjectMethod($reflectionController, 'mapRequestToArguments');

        $map = $mapRequestToArguments($method, $request);

        $this->assertSame(
            [
                'param1' => $expectedValue1,
                'param2' => $expectedValue2,
                'param3' => $expectedValue3,
            ],
            $map
        );

    }

    /**
     * @test
     * @covers ::mapRequestToArguments
     * @uses \AyeAye\Api\ReflectionController::__construct
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ::ayeAyeDeserialize did not return an instance of itself
     */
    public function testMapRequestToArgumentsException()
    {
        $controller = $this->getMockController();
        $reflectionController = new ReflectionController($controller);

        $inputValue3 = 'input value 3';
        $object3 = $this->getMockForAbstractClass(Deserializable::class);
        $object3
            ->expects($this->once())
            ->method('ayeAyeDeserialize')
            ->with($inputValue3)
            ->will($this->returnValue($object3));
        $class3 = $this->getMockReflectionObject();
        $class3
            ->expects($this->once())
            ->method('implementsInterface')
            ->with(Deserializable::class)
            ->will($this->returnValue(true));
        $class3
            ->expects($this->once())
            ->method('newInstanceWithoutConstructor')
            ->with()
            ->will($this->returnValue($object3));
        $class3
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue('invalidClass'));
        $name3 = 'param3';
        $expectedValue3 = $object3;
        $parameter3 = $this->getMockReflectionParameter();
        $parameter3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name3));
        $parameter3
            ->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->will($this->returnValue(false));
        $parameter3
            ->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue($class3));

        $request = $this->getMockRequest();
        $request
            ->expects($this->at(0))
            ->method('getParameter')
            ->with($name3, null)
            ->will($this->returnValue($inputValue3));

        $method = $this->getMockReflectionMethod();
        $method
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue([
                $parameter3,
            ]));

        $mapRequestToArguments = $this->getObjectMethod($reflectionController, 'mapRequestToArguments');

        $map = $mapRequestToArguments($method, $request);

        $this->assertSame(
            [
                'param3' => $expectedValue3,
            ],
            $map
        );

    }

    /**
     * @test
     * @covers ::getStatus
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testGetStatus()
    {
        $status = $this->getMockStatus();

        $controller = $this->getMockController();
        $controller
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $reflectionController = new ReflectionController($controller);

        $this->assertSame(
            $status,
            $reflectionController->getStatus()
        );
    }
}
