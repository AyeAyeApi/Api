<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01/02/2016
 * Time: 23:01
 */

namespace AyeAye\Api\Tests\Injector;

/**
 * Trait WriterFactorInjectorTest
 * @package AyeAye\Api\Tests\Injector
 * @coversDefaultClass \AyeAye\Api\Injector\WriterFactoryInjector
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
