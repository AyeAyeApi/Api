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
     * @covers ::getMethodComment
     */
    public function testGetMethodComment()
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

        $getMethodComment = $this->getObjectMethod($documentor, 'getMethodComment');
        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $this->assertSame(
            $expected,
            $getMethodComment($reflectionMethod)
        );
    }

    /**
     * @test
     * @covers ::getSummary
     * @uses \AyeAye\Api\Documentor::getMethodComment
     */
    public function testgetSummary()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getMethodComment = $this->getObjectMethod($documentor, 'getMethodComment');
        $getSummary = $this->getObjectMethod($documentor, 'getSummary');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "Test Summary\non two lines.",
            $getSummary($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "This is a\nthree line summary\nwith a break",
            $getSummary($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "This is a summary. There is no description",
            $getSummary($comment)
        );
    }

    /**
     * @test
     * @covers ::getDescription
     * @uses \AyeAye\Api\Documentor::getMethodComment
     */
    public function testGetDescription()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getMethodComment = $this->getObjectMethod($documentor, 'getMethodComment');
        $getDescription = $this->getObjectMethod($documentor, 'getDescription');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "Test Description\non\nthree lines.",
            $getDescription($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "This is a one line description",
            $getDescription($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "",
            $getDescription($comment)
        );
    }

    /**
     * @test
     * @covers ::getParameters
     * @uses \AyeAye\Api\Documentor::getMethodComment
     */
    public function testGetParameters()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getMethodComment = $this->getObjectMethod($documentor, 'getMethodComment');
        $getParameters = $this->getObjectMethod($documentor, 'getParameters');

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

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            $expected,
            $getParameters($comment)
        );
    }

    /**
     * @test
     * @covers ::getReturnType()
     * @uses \AyeAye\Api\Documentor::getMethodComment
     */
    public function testGetReturnType()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();

        $getMethodComment = $this->getObjectMethod($documentor, 'getMethodComment');
        $getReturnType = $this->getObjectMethod($documentor, 'getReturnType');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');
        $comment = $getMethodComment($reflectionMethod);

        $this->assertSame(
            ['string'],
            $getReturnType($comment, $reflectionMethod->getDeclaringClass())
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            ['self'],
            $getReturnType($comment, $reflectionMethod->getDeclaringClass())
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            ['null', 'mixed'],
            $getReturnType($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'noDocumentation');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            [],
            $getReturnType($comment)
        );
    }

}