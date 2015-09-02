<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Documenter;
use AyeAye\Api\Tests\TestData\DocumentedController;

/**
 * Class DocumenterTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\Documenter
 */
class DocumenterTest extends TestCase
{

    /**
     * @test
     * @covers ::getMethodDocumentation
     * @uses \AyeAye\Api\Documenter
     */
    public function testGetMethodDocumentation()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();

        $method = new \ReflectionMethod($controller, 'getDocumentedEndpoint');
        $expected = [
            'summary' => "Test Summary\non two lines.",
            'description' => "Test Description\non\nthree lines.",
            'parameters' => [
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
            ],
            'returnType' => ['string']

        ];
        $this->assertSame(
            $expected,
            $documenter->getMethodDocumentation($method)
        );

        $method = new \ReflectionMethod($controller, 'selfReferenceController');
        $expected = [
            'summary' => "This is a\nthree line summary\nwith a break",
            'description' => "This is a one line description",
            'parameters' => [],
            'returnType' => ['self']

        ];
        $this->assertSame(
            $expected,
            $documenter->getMethodDocumentation($method)
        );

        $method = new \ReflectionMethod($controller, 'getNullEndpoint');
        $expected = [
            'summary' => "This is a summary. There is no description",
            'parameters' => [],
            'returnType' => ['null', 'mixed']

        ];
        $this->assertSame(
            $expected,
            $documenter->getMethodDocumentation($method)
        );

        $method = new \ReflectionMethod($controller, 'noDocumentation');
        $expected = [
            'parameters' => [],
            'returnType' => []

        ];
        $this->assertSame(
            $expected,
            $documenter->getMethodDocumentation($method)
        );
    }

    /**
     * @test
     * @covers ::getMethodComment
     */
    public function testGetMethodComment()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();
        $getMethodComment = $this->getObjectMethod($documenter, 'getMethodComment');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');
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
        $this->assertSame(
            $expected,
            $getMethodComment($reflectionMethod)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');
        $expected = [
            '',
            'This is a',
            'three line summary',
            'with a break',
            '',
            'This is a one line description',
            '@return $this'
        ];
        $this->assertSame(
            $expected,
            $getMethodComment($reflectionMethod)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');
        $expected = [
            'This is a summary. There is no description',
            '@return null|mixed',
        ];
        $this->assertSame(
            $expected,
            $getMethodComment($reflectionMethod)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'noDocumentation');
        $expected = [];
        $this->assertSame(
            $expected,
            $getMethodComment($reflectionMethod)
        );
    }

    /**
     * @test
     * @covers ::getSummary
     * @uses \AyeAye\Api\Documenter::getMethodComment
     */
    public function testgetSummary()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();

        $getMethodComment = $this->getObjectMethod($documenter, 'getMethodComment');
        $getSummary = $this->getObjectMethod($documenter, 'getSummary');

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
            'This is a summary. There is no description',
            $getSummary($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'noDocumentation');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            '',
            $getSummary($comment)
        );
    }

    /**
     * @test
     * @covers ::getDescription
     * @uses \AyeAye\Api\Documenter::getMethodComment
     */
    public function testGetDescription()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();

        $getMethodComment = $this->getObjectMethod($documenter, 'getMethodComment');
        $getDescription = $this->getObjectMethod($documenter, 'getDescription');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            "Test Description\non\nthree lines.",
            $getDescription($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            'This is a one line description',
            $getDescription($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            '',
            $getDescription($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'noDocumentation');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            '',
            $getDescription($comment)
        );
    }

    /**
     * @test
     * @covers ::getParameters
     * @uses \AyeAye\Api\Documenter::getMethodComment
     */
    public function testGetParameters()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();

        $getMethodComment = $this->getObjectMethod($documenter, 'getMethodComment');
        $getParameters = $this->getObjectMethod($documenter, 'getParameters');

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

        $reflectionMethod = new \ReflectionMethod($controller, 'selfReferenceController');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            [],
            $getParameters($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'getNullEndpoint');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            [],
            $getParameters($comment)
        );

        $reflectionMethod = new \ReflectionMethod($controller, 'noDocumentation');
        $comment = $getMethodComment($reflectionMethod);
        $this->assertSame(
            [],
            $getParameters($comment)
        );
    }

    /**
     * @test
     * @covers ::getReturnType()
     * @uses \AyeAye\Api\Documenter::getMethodComment
     */
    public function testGetReturnType()
    {
        $controller = new DocumentedController();
        $documenter = new Documenter();

        $getMethodComment = $this->getObjectMethod($documenter, 'getMethodComment');
        $getReturnType = $this->getObjectMethod($documenter, 'getReturnType');

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
