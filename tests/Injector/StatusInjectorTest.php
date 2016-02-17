<?php
/**
 * StatusInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

use AyeAye\Api\Router;

/**
 * Trait StatusInjectorTest
 * Add to the test class for any class that uses the StatusInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait StatusInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::getStatus
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Injector\StatusInjector::setStatus
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

        $router->setStatus($status);
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
     */
    public function testSetStatus()
    {
        $status = $this->getMockStatus();
        $testSubject = $this->getTestSubject();

        $testSubject->setStatus($status);
        $this->assertSame(
            $status,
            $this->getObjectAttribute($testSubject, 'status')
        );
    }
}
