<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 23:50
 */

namespace AyeAye\Api\Tests\Injector;

trait RouterInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::setRouter
     * @uses \AyeAye\Api\Api
     */
    public function testSetRouter()
    {
        // Mocks
        $router = $this->getMockRouter();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'router')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setRouter($router)
        );

        $this->assertSame(
            $router,
            $this->getObjectAttribute($testSubject, 'router')
        );
    }

    /**
     * @test
     * @covers ::getRouter
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Api\Injector\RouterInjector::setRouter
     */
    public function testGetRouter()
    {
        // Mocks
        $router = $this->getMockRouter();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNotSame(
            $router,
            $testSubject->getRouter()
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setRouter($router)
        );

        $this->assertSame(
            $router,
            $testSubject->getRouter()
        );
    }
}
