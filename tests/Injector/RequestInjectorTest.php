<?php
/**
 * RequestInjectorTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

/**
 * Trait RequestInjectorTest
 * Add to the test class for any class that uses the RequestInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait RequestInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::setRequest
     * @uses \AyeAye\Api\Api
     */
    public function testSetRequest()
    {
        // Mocks
        $request = $this->getMockRequest();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'request')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setRequest($request)
        );

        $this->assertSame(
            $request,
            $this->getObjectAttribute($testSubject, 'request')
        );
    }

    /**
     * @test
     * @covers ::getRequest
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Api\Injector\RequestInjector::setRequest
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequest()
    {
        // Mocks
        $request = $this->getMockRequest();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNotSame(
            $request,
            $testSubject->getRequest()
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setRequest($request)
        );

        $this->assertSame(
            $request,
            $testSubject->getRequest()
        );
    }
}
