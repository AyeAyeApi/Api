<?php
/**
 * ControllerReflectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
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
