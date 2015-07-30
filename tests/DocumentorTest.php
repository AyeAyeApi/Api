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
 * @coversDefaultClass \AyeAye\Api\Documentor
 */
class DocumentorTest extends TestCase
{

    /**
     * @test
     * @covers ::getDocComment
     */
    public function testGetDocComment()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $expected = [
            'Test Summary',
            'on two lines.',
            'Test Description',
            'on',
            'three lines.',
            '@param        $incomplete',
            '@param int    $integer    Test integer',
            '@param string $string     Test string',
            'Second line',
            '@return string'
        ];

        $getDocComment = $this->getObjectMethod($documentor, 'getDocComment');
        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $this->assertSame(
            $expected,
            $getDocComment($reflectionMethod)
        );
    }

    /**
     * @test
     * @covers ::getMethodSummary
     * @uses \AyeAye\Api\Documentor::getDocComment
     */
    public function testGetMethodSummary()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getDocComment = $this->getObjectMethod($documentor, 'getDocComment');
        $getMethodSummary = $this->getObjectMethod($documentor, 'getMethodSummary');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $comment = $getDocComment($reflectionMethod);
        $this->assertSame(
            "Test Summary\non two lines.",
            $getMethodSummary($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');

        $comment = $getDocComment($reflectionMethod);
        $this->assertSame(
            "This is a\nthree line summary\nwith a break",
            $getMethodSummary($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');

        $comment = $getDocComment($reflectionMethod);
        $this->assertSame(
            "This is a summary. There is no description",
            $getMethodSummary($comment)
        );
    }

    /**
     * @test
     * @covers ::getMethodParameters
     * @uses \AyeAye\Api\Documentor::getDocComment
     */
    public function testGetMethodParameters()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getDocComment = $this->getObjectMethod($documentor, 'getDocComment');
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

        $comment = $getDocComment($reflectionMethod);
        $this->assertSame(
            $expected,
            $getMethodParameters($comment)
        );
    }

}