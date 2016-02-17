<?php
/**
 * ApiTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\Tests\Injector\StatusInjectorTest;


/**
 * Class ControllerTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass \AyeAye\Api\Controller
 */
class ControllerTest extends TestCase
{
    use StatusInjectorTest;

    /**
     * @return Controller
     */
    protected function getTestSubject()
    {
        return new Controller();
    }

    /**
     * @test
     * @covers ::hideMethod
     * @return void
     */
    public function testHideMethod()
    {
        // Test Data
        $method = 'hideMethod';

        // Tests
        $controller = new Controller();

        $hiddenMethods = $this->getObjectAttribute($controller, 'hiddenMethods');

        $this->assertCount(
            1,
            $hiddenMethods
        );

        $this->assertArrayHasKey(
            'getIndexEndpoint',
            $hiddenMethods
        );

        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $hideMethod($method);

        $hiddenMethods = $this->getObjectAttribute($controller, 'hiddenMethods');

        $this->assertCount(
            2,
            $hiddenMethods
        );

        $this->assertArrayHasKey(
            $method,
            $hiddenMethods
        );
    }


    /**
     * @test
     * @covers ::hideMethod
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessage The method 'notARealMethod' does not exist in AyeAye\Api\Controller
     * @return void
     */
    public function testHideMethodException()
    {
        // Test Data
        $method = 'notARealMethod';

        // Tests
        $controller = new Controller();

        $hideMethod = $this->getObjectMethod($controller, 'hideMethod');
        $hideMethod($method);
    }

    /**
     * @test
     * @covers ::isMethodHidden
     * @return void
     */
    public function testIsMethodHidden()
    {
        // Test Data
        $method = 'hideMethod';

        // Tests
        $controller = new Controller();

        $isMethodHidden = $this->getObjectMethod($controller, 'isMethodHidden');

        $this->assertFalse(
            $isMethodHidden($method)
        );

        $this->setObjectAttribute($controller, 'hiddenMethods', [$method => true]);

        $this->assertTrue(
            $isMethodHidden($method)
        );
    }

    /**
     * @test
     * @covers ::isMethodHidden
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Status
     * @expectedException \AyeAye\Api\Exception
     * @expectedExceptionCode 500
     * @expectedExceptionMessage The method 'notARealMethod' does not exist in AyeAye\Api\Controller
     * @return void
     */
    public function testIsMethodHiddenException()
    {
        // Test Data
        $method = 'notARealMethod';

        // Tests
        $controller = new Controller();

        $isMethodHidden = $this->getObjectMethod($controller, 'isMethodHidden');
        $isMethodHidden($method);
    }

    /**
     * @test
     * @covers ::showMethod
     * @uses \AyeAye\Api\Controller::isMethodHidden
     * @return void
     */
    public function testShowMethod()
    {
        // Test Data
        $hiddenMethod = 'hideMethod';
        $visibleMethod = 'showMethod';

        // Tests
        $controller = new Controller();

        $hiddenMethods = [$hiddenMethod => true];
        $this->setObjectAttribute($controller, 'hiddenMethods', $hiddenMethods);

        $showMethod = $this->getObjectMethod($controller, 'showMethod');

        // Method is not hidden, nothing should happen
        $this->assertSame(
            $controller,
            $showMethod($visibleMethod)
        );

        $this->assertSame(
            $hiddenMethods,
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );

        // Method is not hidden, nothing should happen
        $this->assertSame(
            $controller,
            $showMethod($hiddenMethod)
        );

        $this->assertNotSame(
            $hiddenMethods,
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );

        $this->assertEmpty(
            $this->getObjectAttribute($controller, 'hiddenMethods')
        );
    }
}
