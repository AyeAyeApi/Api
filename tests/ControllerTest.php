<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\Status;


/**
 * Class ControllerTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\Controller
 */
class ControllerTest extends TestCase
{

    /**
     * @test
     * @covers ::getStatus
     * @covers ::setStatus
     * @uses \AyeAye\Api\Status
     */
    public function testStatus()
    {
        $controller = new Controller();
        $status = new Status();

        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $controller->getStatus()
        );
        $this->assertSame(
            200,
            $controller->getStatus()->getCode()
        );

        $setStatus = $this->getObjectMethod($controller, 'setStatus');
        $this->assertSame(
            $controller,
            $setStatus($status)
        );
        $this->assertSame(
            $status,
            $controller->getStatus()
        );
    }

    /**
     * @test
     * @covers ::setStatusCode
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Controller::setStatus
     * @uses \AyeAye\Api\Controller::getStatus
     */
    public function testStatusCode()
    {
        $code = 418;
        $controller = new Controller();

        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $controller->getStatus()
        );
        $this->assertSame(
            200,
            $controller->getStatus()->getCode()
        );

        $setStatusCode = $this->getObjectMethod($controller, 'setStatusCode');
        $this->assertSame(
            $controller,
            $setStatusCode(418)
        );
        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $controller->getStatus()
        );
        $this->assertSame(
            $code,
            $controller->getStatus()->getCode()
        );
    }

    /**
     * @test
     * @covers ::hideMethod
     * @covers ::isMethodHidden
     * @covers ::showMethod
     */
    public function testHideMethod()
    {
        $controller = new Controller();
        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $showMethod = $this->getObjectMethod($controller, 'showMethod');
        $methodName = 'getStatus';

        $this->assertFalse(
            $controller->isMethodHidden($methodName)
        );
        $this->assertSame(
            $controller,
            $hideMethod($methodName)
        );
        $this->assertTrue(
            $controller->isMethodHidden($methodName)
        );
        $this->assertSame(
            $controller,
            $showMethod($methodName)
        );
        $this->assertFalse(
            $controller->isMethodHidden($methodName)
        );
    }

    /**
     * @test
     * @covers ::hideMethod
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessageRegExp /^The method \'\w+\' does not exist in \S+$/
     */
    public function testHideMethodException()
    {
        $controller = new Controller();
        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $hideMethod('nonexistentMethod');
    }

    /**
     * @test
     * @covers ::isMethodHidden
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessageRegExp /^The method \'\w+\' does not exist in \S+$/
     */
    public function testIsMethodHiddenException()
    {
        $controller = new Controller();
        $isMethodHidden = $this->getObjectMethod($controller, 'isMethodHidden');
        $isMethodHidden('nonexistentMethod');
    }

    /**
     * @test
     * @covers ::showMethod
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessageRegExp /^The method \'\w+\' does not exist in \S+$/
     */
    public function testShowMethodException()
    {
        $controller = new Controller();
        $showMethod = $this->getObjectMethod($controller, 'showMethod');
        $showMethod('nonexistentMethod');
    }

}