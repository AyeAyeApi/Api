<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 23:47
 */

namespace AyeAye\Api\Tests\Injector;

trait ResponseInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::setResponse
     * @uses \AyeAye\Api\Api
     */
    public function testSetResponse()
    {
        // Mocks
        $response = $this->getMockResponse();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'response')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setResponse($response)
        );

        $this->assertSame(
            $response,
            $this->getObjectAttribute($testSubject, 'response')
        );
    }

    /**
     * @test
     * @covers ::getResponse
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Api\Injector\ResponseInjector::setResponse
     * @uses \AyeAye\Api\Response
     */
    public function testGetResponse()
    {
        // Mocks
        $response = $this->getMockResponse();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNotSame(
            $response,
            $testSubject->getResponse()
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setResponse($response)
        );

        $this->assertSame(
            $response,
            $testSubject->getResponse()
        );
    }
}
