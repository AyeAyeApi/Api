<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/12/2015
 * Time: 13:58
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
     * @covers ::getDocumentation
     * @uses \AyeAye\Api\ControllerDocumentation
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testDocumentController()
    {
        $controller = new Controller();
        $reflection = new ReflectionController($controller);

        $documentController = $this->getObjectMethod($reflection, 'getDocumentation');

        $getDocumentation = $documentController($controller);

        $this->assertInstanceOf(
            ControllerDocumentation::class,
            $getDocumentation
        );

        $this->assertSame(
            Controller::class,
            $this->getObjectAttribute($getDocumentation, 'reflectedController')->getName()
        );
    }

    /**
     * @test
     * @covers ::parseEndpointName
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testParseEndpointName()
    {
        $controller = new Controller();
        $reflection = new ReflectionController($controller);

        $parseEndpointName = $this->getObjectMethod($reflection, 'parseEndpointName');


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
     * @covers ::parseControllerName
     * @uses \AyeAye\Api\ReflectionController::__construct
     */
    public function testParseControllerName()
    {
        $controller = new Controller();
        $reflection = new ReflectionController($controller);

        $parseControllerName = $this->getObjectMethod($reflection, 'parseControllerName');

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
        $controller = new Controller();
        $reflection = new ReflectionController($controller);

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

        $mapRequestToArguments = $this->getObjectMethod($reflection, 'mapRequestToArguments');

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
        $controller = new Controller();
        $reflection = new ReflectionController($controller);

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

        $mapRequestToArguments = $this->getObjectMethod($reflection, 'mapRequestToArguments');

        $map = $mapRequestToArguments($method, $request);

        $this->assertSame(
            [
                'param3' => $expectedValue3,
            ],
            $map
        );

    }
}
