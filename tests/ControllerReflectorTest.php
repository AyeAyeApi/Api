<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07/02/2016
 * Time: 17:24
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\ControllerReflector;
use AyeAye\Api\ReflectionController;

/**
 * Class ControllerReflectorTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\ControllerReflector
 */
class ControllerReflectorTest extends TestCase
{
    /**
     * @test
     * @covers ::reflectController
     * @uses \AyeAye\Api\ReflectionController
     */
    public function testReflectController()
    {
        $controller = $this->getMockController();
        $controllerReflector = new ControllerReflector();

        $reflectionController = $controllerReflector->reflectController($controller);

        $this->assertInstanceOf(
            ReflectionController::class,
            $reflectionController
        );
        $this->assertSame(
            $controller,
            $this->getObjectAttribute($reflectionController, 'controller')
        );
    }
}
