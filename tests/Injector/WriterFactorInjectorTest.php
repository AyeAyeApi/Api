<?php
/**
 * WriterFactorInjectorTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests\Injector;

/**
 * Trait WriterFactorInjectorTest
 * Add to the test class for any class that uses the WriterFactorInjector trait
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait WriterFactorInjectorTest
{
    use InjectorTestTrait;

    /**
     * @test
     * @covers ::setWriterFactory
     * @uses \AyeAye\Api\Api
     */
    public function testSetWriterFactory()
    {
        // Mocks
        $writerFactory = $this->getMockWriterFactory();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNull(
            $this->getObjectAttribute($testSubject, 'writerFactory')
        );

        $this->assertSame(
            $testSubject,
            $testSubject->setWriterFactory($writerFactory)
        );

        $this->assertSame(
            $writerFactory,
            $this->getObjectAttribute($testSubject, 'writerFactory')
        );
    }

    /**
     * @test
     * @covers ::getWriterFactory
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector::setWriterFactory
     * @uses \AyeAye\Api\Api
     * @uses \AyeAye\Formatter\WriterFactory
     */
    public function testGetWriterFactory()
    {
        // Test Data
        $requiredFormats = [
            'xml',
            'text/xml',
            'application/xml',
            'json',
            'application/json',
        ];

        // Mocks
        $writerFactory = $this->getMockWriterFactory();

        // Tests
        $testSubject = $this->getTestSubject();

        $this->assertNotSame(
            $writerFactory,
            $testSubject->getWriterFactory()
        );

        // Test the factory was constructed correctly
        $formats = $this->getObjectAttribute($testSubject->getWriterFactory(), 'formats');
        foreach($requiredFormats as $format) {
            $this->assertArrayHasKey(
                $format,
                $formats
            );
        }

        $this->assertSame(
            $testSubject,
            $testSubject->setWriterFactory($writerFactory)
        );

        $this->assertSame(
            $writerFactory,
            $testSubject->getWriterFactory()
        );
    }
}
