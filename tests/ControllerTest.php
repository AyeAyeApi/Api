<?php
/**
 * ControllerTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;


/**
 * Class ControllerTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass \AyeAye\Api\Controller
 */
class ControllerTest extends TestCase
{

    /**
     * @test
     * @covers ::getStatus
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Controller::setStatus
     * @return void
     */
    public function testGetStatus()
    {
        // Mocks
        $status = $this->getMockStatus();

        // Tests
        $controller = new Controller();

        $result = $controller->getStatus();

        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $result
        );

        $this->assertNotSame(
            $status,
            $result
        );

        $this->setObjectAttribute($controller, 'status', $status);

        $result = $controller->getStatus();

        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $result
        );

        $this->assertSame(
            $status,
            $result
        );
    }

    /**
     * @test
     * @covers ::setStatus
     * @uses \AyeAye\Api\Status
     * @return void
     */
    public function testSetStatus()
    {
        // Mocks
        $status = $this->getMockStatus();

        // Tests
        $controller = new Controller();

        $this->assertNull(
            $this->getObjectAttribute($controller, 'status')
        );

        $setStatus = $this->getObjectMethod($controller, 'setStatus');

        $this->assertSame(
            $controller,
            $setStatus($status)
        );

        $this->assertSame(
            $status,
            $this->getObjectAttribute($controller, 'status')
        );

    }

    /**
     * @test
     * @covers ::setStatusCode
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Controller::setStatus
     * @return void
     */
    public function testSetStatusCode()
    {
        // Test Data
        $statusCode = 418;

        // Tests
        $controller = new Controller();

        $this->assertNull(
            $this->getObjectAttribute($controller, 'status')
        );

        $setStatusCode = $this->getObjectMethod($controller, 'setStatusCode');

        $this->assertSame(
            $controller,
            $setStatusCode($statusCode)
        );

        $this->assertSame(
            $statusCode,
            $this->getObjectAttribute($controller, 'status')->getCode()
        );

    }

    /**
     * @test
     * @covers ::hideMethod
     * @return void
     */
    public function testHideMethod()
    {
        // Test Data
        $method = 'hideMethod';

        // Tests
        $controller = new Controller();

        $hiddenMethods = $this->getObjectAttribute($controller, 'hiddenMethods');

        $this->assertCount(
            1,
            $hiddenMethods
        );

        $this->assertArrayHasKey(
            'getIndexEndpoint',
            $hiddenMethods
        );

        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $hideMethod($method);

        $hiddenMethods = $this->getObjectAttribute($controller, 'hiddenMethods');

        $this->assertCount(
            2,
            $hiddenMethods
        );

        $this->assertArrayHasKey(
            $method,
            $hiddenMethods
        );
    }


    /**
     * @test
     * @covers ::hideMethod
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessage The method 'notARealMethod' does not exist in AyeAye\Api\Controller
     * @return void
     */
    public function testHideMethodException()
    {
        // Test Data
        $method = 'notARealMethod';

        // Tests
        $controller = new Controller();

        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $hideMethod($method);
    }

    /**
     * @test
     * @covers ::isMethodHidden
     * @return void
     */
    public function testIsMethodHidden()
    {
        // Test Data
        $method = 'hideMethod';

        // Tests
        $controller = new Controller();

        $isMethodHidden = $this->getObjectMethod($controller, 'isMethodHidden');

        $this->assertFalse(
            $isMethodHidden($method)
        );

        $this->setObjectAttribute($controller, 'hiddenMethods', [$method => true]);

        $this->assertTrue(
            $isMethodHidden($method)
        );
    }

    /**
     * @test
     * @covers ::isMethodHidden
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessage The method 'notARealMethod' does not exist in AyeAye\Api\Controller
     * @return void
     */
    public function testIsMethodHiddenException()
    {
        // Test Data
        $method = 'notARealMethod';

        // Tests
        $controller = new Controller();

        $isMethodHidden = $this->getObjectMethod($controller, 'isMethodHidden');
        $isMethodHidden($method);
    }

    /**
     * @test
     * @covers ::showMethod
     * @uses \AyeAye\Api\Controller::isMethodHidden
     * @return void
     */
    public function testShowMethod()
    {
        // Test Data
        $hiddenMethod = 'hideMethod';
        $visibleMethod = 'showMethod';

        // Tests
        $controller = new Controller();

        $hiddenMethods = [$hiddenMethod => true];
        $this->setObjectAttribute($controller, 'hiddenMethods', $hiddenMethods);

        $showMethod = $this->getObjectMethod($controller, 'showMethod');

        // Method is not hidden, nothing should happen
        $this->assertSame(
            $controller,
            $showMethod($visibleMethod)
        );

        $this->assertSame(
            $hiddenMethods,
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );

        // Method is not hidden, nothing should happen
        $this->assertSame(
            $controller,
            $showMethod($hiddenMethod)
        );

        $this->assertNotSame(
            $hiddenMethods,
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );

        $this->assertEmpty(
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );
    }

    /**
     * @test
     * @covers ::showMethod
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessage The method 'notARealMethod' does not exist in AyeAye\Api\Controller
     * @return void
     */
    public function testShowMethodException()
    {
        // Test Data
        $method = 'notARealMethod';

        // Tests
        $controller = new Controller();

        $showMethod = $this->getObjectMethod($controller, 'showMethod');
        $showMethod($method);
    }
}
