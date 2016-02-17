<?php
/**
 * ResponseInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

/**
 * Trait ResponseInjectorTest
 * Add to the test class for any class that uses the ResponseInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
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
