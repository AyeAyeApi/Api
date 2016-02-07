<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 22:37
 */

namespace AyeAye\Api\Tests\Injector;

use AyeAye\Api\ControllerReflector;

/**
 * Trait ControllerReflectorTest
 * @package AyeAye\Api\Tests\Injector
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
