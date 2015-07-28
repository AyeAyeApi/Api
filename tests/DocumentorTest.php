<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;


use AyeAye\Api\Documentor;
use AyeAye\Api\Tests\TestData\DocumentedController;

/**
 * Class DocumentorTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass AyeAye\Api\Documentor
 */
class DocumentorTest extends TestCase
{

    /**
     * @test
     * @covers ::getMethodSummary
     */
    public function testGetMethodSummary()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();
        $getMethodSummary = $this->getObjectMethod($documentor, 'getMethodSummary');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $this->assertSame(
            "Test Summary\non two lines.",
            $getMethodSummary($reflectionMethod)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');

        $this->assertSame(
            "This is a\nthree line summary\nwith a break",
            $getMethodSummary($reflectionMethod)
        );
    }

    /**
     * @test
     * @covers ::getMethodParameters
     */
    public function testGetMethodParameters()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();
        $getMethodParameters = $this->getObjectMethod($documentor, 'getMethodParameters');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $expected = [
            'incomplete' => [
                'type' => '',
                'description' => '',
            ],
            'integer' => [
                'type' => 'int',
                'description' => 'Test integer',
            ],
            'string' => [
                'type' => 'string',
                'description' => "Test string\nSecond line",
            ],
        ];

        $this->assertSame(
            $expected,
            $getMethodParameters($reflectionMethod)
        );
    }

}