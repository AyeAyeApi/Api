<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 23:45
 */

namespace AyeAye\Api\Tests\Injector;

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
