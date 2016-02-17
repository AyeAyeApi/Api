<?php
/**
 * ControllerDocumentationTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\ControllerDocumentation;

/**
 * Class ControllerDocumentationTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass AyeAye\Api\ControllerDocumentation
 */
class ControllerDocumentationTest extends TestCase
{

    /**
     * @return \ReflectionObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockReflectedController()
    {
        $mockReflection = $this->getMockReflectionObject();
        $mockReflection
            ->expects($this->once())
            ->method('isSubclassOf')
            ->with(Controller::class)
            ->will($this->returnValue(true));
        return $mockReflection;
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);

        $this->assertSame(
            $mockReflection,
            $this->getObjectAttribute($controllerDocumentation, 'reflectedController')
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The ControllerDocumentation class can only document Controllers
     */
    public function testConstructException()
    {
        /** @var \ReflectionObject|\PHPUnit_Framework_MockObject_MockObject $mockReflection */
        $mockReflection = $this
            ->getMockBuilder('\ReflectionObject')
            ->disableOriginalConstructor()
            ->getMock();
        $mockReflection
            ->expects($this->once())
            ->method('isSubclassOf')
            ->with(Controller::class)
            ->will($this->returnValue(false));

        new ControllerDocumentation($mockReflection);
    }

    /**
     * @test
     * @covers ::getEndpoints
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     * @uses \AyeAye\Api\ControllerDocumentation::getControllerMethod
     * @uses \AyeAye\Api\ControllerDocumentation::camelcaseToHyphenated
     */
    public function testGetEndpoints()
    {
        // Test the actual generation
        $methodName1 = 'getFirstEndpoint';
        $methodName2 = 'getSecondEndpoint';
        $methodName3 = 'postComplexlyNamedEndpoint';
        $methodName4 = 'postHiddenEndpoint';

        $reflectedMethod = $this->getMockReflectionMethod();
        $reflectedMethod
            ->expects($this->any())
            ->method('invokeArgs')
            ->will($this->returnCallback(function($controller, $inputArray) {
                return reset($inputArray) == 'postHiddenEndpoint';
            }));

        $method1 = $this->getMockReflectionMethod();
        $method1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName1));
        $method2 = $this->getMockReflectionMethod();
        $method2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName2));
        $method3 = $this->getMockReflectionMethod();
        $method3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName3));
        $method4 = $this->getMockReflectionMethod();
        $method4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName4));

        $mockReflection = $this->getMockReflectedController();
        $mockReflection
            ->expects($this->once())
            ->method('getMethods')
            ->with(\ReflectionMethod::IS_PUBLIC)
            ->will($this->returnValue([
                $method1,
                $method2,
                $method3,
                $method4,
            ]));
        $mockReflection
            ->expects($this->once())
            ->method('getMethod')
            ->with('isMethodHidden')
            ->will($this->returnValue($reflectedMethod));

        $documentation = $this->getMockDocumentation();
        $documentation
            ->expects($this->any())
            ->method('getMethodDocumentation')
            ->will($this->returnValue([]));

        $controllerDocumentation = new ControllerDocumentation($mockReflection);
        $this->setObjectAttribute($controllerDocumentation, 'documentation', $documentation);

        $getEndpoints = $this->getObjectMethod($controllerDocumentation, 'getEndpoints');

        $this->assertSame(
            [
                'get' => [
                    'first' => [],
                    'second' => [],
                ],
                'post' => [
                    'complexly-named' => [],
                ]

            ],
            $getEndpoints()
        );
    }

    /**
     * @test
     * @covers ::getEndpoints
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     */
    public function testGetEndpointsSimple()
    {
        // Quick check
        $endpoints = new \stdClass();

        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);
        $this->setObjectAttribute($controllerDocumentation, 'endpointsCache', $endpoints);
        $getEndpoints = $this->getObjectMethod($controllerDocumentation, 'getEndpoints');

        $this->assertSame(
            $endpoints,
            $getEndpoints()
        );
    }

    /**
     * @test
     * @covers ::getControllers
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     * @uses \AyeAye\Api\ControllerDocumentation::getControllerMethod
     * @uses \AyeAye\Api\ControllerDocumentation::camelcaseToHyphenated
     */
    public function testGetControllers()
    {
        // Test the actual generation
        $methodName1 = 'simpleController';
        $methodName2 = 'complexlyNamedController';
        $methodName3 = 'hiddenController';
        $methodName4 = 'controllerNope';

        $reflectedMethod = $this->getMockReflectionMethod();
        $reflectedMethod
            ->expects($this->any())
            ->method('invokeArgs')
            ->will($this->returnCallback(function($controller, $inputArray) {
                return reset($inputArray) == 'hiddenController';
            }));

        $method1 = $this->getMockReflectionMethod();
        $method1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName1));
        $method2 = $this->getMockReflectionMethod();
        $method2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName2));
        $method3 = $this->getMockReflectionMethod();
        $method3
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName3));
        $method4 = $this->getMockReflectionMethod();
        $method4
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($methodName4));

        $mockReflection = $this->getMockReflectedController();
        $mockReflection
            ->expects($this->once())
            ->method('getMethods')
            ->with(\ReflectionMethod::IS_PUBLIC)
            ->will($this->returnValue([
                $method1,
                $method2,
                $method3,
                $method4,
            ]));
        $mockReflection
            ->expects($this->once())
            ->method('getMethod')
            ->with('isMethodHidden')
            ->will($this->returnValue($reflectedMethod));

        $controllerDocumentation = new ControllerDocumentation($mockReflection);

        $getControllers = $this->getObjectMethod($controllerDocumentation, 'getControllers');

        $this->assertSame(
            [
                'simple',
                'complexly-named',
            ],
            $getControllers()
        );
    }

    /**
     * @test
     * @covers ::getControllers
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     */
    public function testGetControllersSimple()
    {
        // Easy to track
        $controllers = new \stdClass();

        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);
        $this->setObjectAttribute($controllerDocumentation, 'controllersCache', $controllers);
        $getControllers = $this->getObjectMethod($controllerDocumentation, 'getControllers');

        $this->assertSame(
            $controllers,
            $getControllers()
        );
    }

    /**
     * @test
     * @covers ::camelcaseToHyphenated
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     */
    public function testCamelcaseToHyphenated()
    {
        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);

        $camelcaseToHyphenated = $this->getObjectMethod($controllerDocumentation, 'camelcaseToHyphenated');

        $this->assertSame(
            'camelcase-to-hyphenated',
            $camelcaseToHyphenated('camelcaseToHyphenated')
        );
    }

    /**
     * @test
     * @covers ::getControllerMethod
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     */
    public function testGetControllerMethod()
    {
        $param1 = 'param 1';
        $param2 = 'param 2';
        $methodName = 'methodName';

        $mockReflectedObject = $this->getMockReflectedController();

        /** @var \ReflectionMethod|\PHPUnit_Framework_MockObject_MockObject $mockReflectedMethod */
        $mockReflectedMethod = $this->getMockReflectionMethod();
        $mockReflectedMethod
            ->expects($this->once())
            ->method('setAccessible')
            ->with(true)
            ->will($this->returnValue(null));
        $mockReflectedMethod
            ->expects($this->once())
            ->method('invokeArgs')
            ->with($mockReflectedObject, [$param1, $param2])
            ->will($this->returnValue(true));

        $mockReflectedObject
            ->expects($this->once())
            ->method('getMethod')
            ->with($methodName)
            ->will($this->returnValue($mockReflectedMethod));

        $controllerDocumentation = new ControllerDocumentation($mockReflectedObject);

        $getControllerMethod = $this->getObjectMethod($controllerDocumentation, 'getControllerMethod');

        $controllerMethod = $getControllerMethod($methodName);

        $this->assertTrue(
            $controllerMethod($param1, $param2)
        );
    }

    /**
     * @test
     * @covers ::getDocumentation
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     * @uses \AyeAye\Api\ControllerDocumentation::getControllers
     * @uses \AyeAye\Api\ControllerDocumentation::getEndpoints
     */
    public function testGetDocumentation()
    {
        // Easy to track
        $endpoints = new \stdClass();
        $controllers = new \stdClass();

        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);

        $this->setObjectAttribute($controllerDocumentation, 'endpointsCache', $endpoints);
        $this->setObjectAttribute($controllerDocumentation, 'controllersCache', $controllers);

        $this->assertSame(
            [
                'controllers' => $controllers,
                'endpoints' => $endpoints,
            ],
            $controllerDocumentation->getDocumentation()
        );
    }

    /**
     * @test
     * @covers ::ayeAyeSerialize
     * @uses \AyeAye\Api\ControllerDocumentation::__construct
     * @uses \AyeAye\Api\ControllerDocumentation::getControllers
     * @uses \AyeAye\Api\ControllerDocumentation::getEndpoints
     * @uses \AyeAye\Api\ControllerDocumentation::getDocumentation
     */
    public function testAyeAyeSerialize()
    {
        // Easy to track
        $endpoints = new \stdClass();
        $controllers = new \stdClass();

        $mockReflection = $this->getMockReflectedController();

        $controllerDocumentation = new ControllerDocumentation($mockReflection);

        $this->setObjectAttribute($controllerDocumentation, 'endpointsCache', $endpoints);
        $this->setObjectAttribute($controllerDocumentation, 'controllersCache', $controllers);

        $this->assertSame(
            [
                'controllers' => $controllers,
                'endpoints' => $endpoints,
            ],
            $controllerDocumentation->ayeAyeSerialize()
        );
    }

}
