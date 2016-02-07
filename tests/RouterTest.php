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
