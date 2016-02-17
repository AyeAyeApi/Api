<?php
/**
 * RouterInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

/**
 * Trait RouterInjectorTest
 * Add to the test class for any class that uses the RouterInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
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
