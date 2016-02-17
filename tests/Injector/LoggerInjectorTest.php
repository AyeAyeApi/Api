<?php
/**
 * LoggerInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

use Psr\Log\NullLogger;

/**
 * Trait LoggerInjectorTest
 * Add to the test class for any class that uses the LoggerInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
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
