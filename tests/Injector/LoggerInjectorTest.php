<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 04/02/2016
 * Time: 22:37
 */

namespace AyeAye\Api\Tests\Injector;

use Psr\Log\NullLogger;

trait LoggerInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::getLogger
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Api\Injector\LoggerInjector::setLogger
     */
    public function testGetLogger()
    {
        // Mocks
        $logger = $this->getMockLogger();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertInstanceOf(
            NullLogger::class,
            $testSubject->getLogger()
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setLogger($logger)
        );

        $this->assertSame(
            $logger,
            $testSubject->getLogger($testSubject, 'logger')
        );
    }

    /**
     * @test
     * @covers ::setLogger
     * @uses \AyeAye\Api\Api
     */
    public function testSetLogger()
    {
        // Mocks
        $logger = $this->getMockLogger();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'logger')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setLogger($logger)
        );

        $this->assertSame(
            $logger,
            $this->getObjectAttribute($testSubject, 'logger')
        );
    }
}
