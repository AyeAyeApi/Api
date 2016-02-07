<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07/02/2016
 * Time: 23:11
 */

namespace AyeAye\Api\Tests\Injector;

use AyeAye\Api\Router;

/**
 * Trait StatusInjectorTest
 * @package AyeAye\Api\Tests\Injector
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
