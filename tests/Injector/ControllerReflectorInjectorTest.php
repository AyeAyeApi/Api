<?php
/**
 * ControllerReflectorInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

use AyeAye\Api\ControllerReflector;

/**
 * Trait ControllerReflectorInjectorTest
 * Add to the test class for any class that uses the ControllerReflectorInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait ControllerReflectorInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::getControllerReflector
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Api\Injector\ControllerReflectorInjector::setControllerReflector
     */
    public function testGetControllerReflector()
    {
        // Mocks
        $controllerReflector = $this->getMockControllerReflector();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertInstanceOf(
            ControllerReflector::class,
            $testSubject->getControllerReflector()
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setControllerReflector($controllerReflector)
        );

        $this->assertSame(
            $controllerReflector,
            $testSubject->getControllerReflector($testSubject, 'controllerReflector')
        );
    }

    /**
     * @test
     * @covers ::setControllerReflector
     * @uses \AyeAye\Api\Api
     */
    public function testSetControllerReflector()
    {
        // Mocks
        $controllerReflector = $this->getMockControllerReflector();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'controllerReflector')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setControllerReflector($controllerReflector)
        );

        $this->assertSame(
            $controllerReflector,
            $this->getObjectAttribute($testSubject, 'controllerReflector')
        );
    }
}
