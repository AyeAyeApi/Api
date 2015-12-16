<?php
/**
 * DocumentationTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Documentation;

/**
 * Class DocumentationTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass \AyeAye\Api\Documentation
 */
class DocumentationTest extends TestCase
{

    /**
     * @param string $documentation
     * @return \ReflectionMethod|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMethodWithDocBlock($documentation = '')
    {
        $reflectionMethod = $this
            ->getMockBuilder('ReflectionMethod')
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionMethod
            ->method('getDocComment')
            ->with()
            ->will($this->returnValue($documentation));
        return $reflectionMethod;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\ReflectionMethod
     */
    protected function createDocBlock()
    {
        return $this->createMethodWithDocBlock('/**
             * Test Summary
             * on two lines.
             * Test Description
             * on
             * three lines.
             * @param        $incomplete
             * @param int    $integer    Test integer
             * @param string $string     Test string
             * Second line
             * @return string
             */');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\ReflectionMethod
     */
    protected function createDocBlockWithLineBreak()
    {
        return $this->createMethodWithDocBlock('/**
             *
             * This is a
             * three line summary
             * with a break
             *
             * This is a one line description
             * @return $this
             */');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\ReflectionMethod
     */
    protected function createDocBlockNoDescriptionMultiReturnType()
    {
        return $this->createMethodWithDocBlock('/**
             * This is a summary. There is no description
             * @return null|mixed
             */');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\ReflectionMethod
     */
    protected function createEmptyDocBlock()
    {
        return $this->createMethodWithDocBlock('');
    }

    /**
     * @test
     * @covers ::getMethodDocumentation
     * @uses \AyeAye\Api\Documentation
     */
    public function testGetMethodDocumentation()
    {
        $documentation = new Documentation();

        $method = $this->createDocBlock();
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
            $documentation->getMethodDocumentation($method)
        );

        $method = $this->createDocBlockWithLineBreak();
        $expected = [
            'summary' => "This is a\nthree line summary\nwith a break",
            'description' => "This is a one line description",
            'parameters' => [],
            'returnType' => ['self']

        ];
        $this->assertSame(
            $expected,
            $documentation->getMethodDocumentation($method)
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $expected = [
            'summary' => "This is a summary. There is no description",
            'parameters' => [],
            'returnType' => ['null', 'mixed']

        ];
        $this->assertSame(
            $expected,
            $documentation->getMethodDocumentation($method)
        );

        $method = $this->createEmptyDocBlock();
        $expected = [
            'parameters' => [],
            'returnType' => []

        ];
        $this->assertSame(
            $expected,
            $documentation->getMethodDocumentation($method)
        );
    }

    /**
     * @test
     * @covers ::getMethodComment
     */
    public function testGetMethodComment()
    {
        $documentation = new Documentation();
        $getMethodComment = $this->getObjectMethod($documentation, 'getMethodComment');

        $method = $this->createDocBlock();
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
            $getMethodComment($method)
        );

        $method = $this->createDocBlockWithLineBreak();
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
            $getMethodComment($method)
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $expected = [
            'This is a summary. There is no description',
            '@return null|mixed',
        ];
        $this->assertSame(
            $expected,
            $getMethodComment($method)
        );

        $method = $this->createEmptyDocBlock();
        $expected = [];
        $this->assertSame(
            $expected,
            $getMethodComment($method)
        );
    }

    /**
     * @test
     * @covers ::getSummary
     * @uses \AyeAye\Api\Documentation::getMethodComment
     */
    public function testgetSummary()
    {
        $documentation = new Documentation();

        $getMethodComment = $this->getObjectMethod($documentation, 'getMethodComment');
        $getSummary = $this->getObjectMethod($documentation, 'getSummary');

        $method = $this->createDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            "Test Summary\non two lines.",
            $getSummary($comment)
        );

        $method = $this->createDocBlockWithLineBreak();
        $comment = $getMethodComment($method);
        $this->assertSame(
            "This is a\nthree line summary\nwith a break",
            $getSummary($comment)
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $comment = $getMethodComment($method);
        $this->assertSame(
            'This is a summary. There is no description',
            $getSummary($comment)
        );

        $method = $this->createEmptyDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            '',
            $getSummary($comment)
        );
    }

    /**
     * @test
     * @covers ::getDescription
     * @uses \AyeAye\Api\Documentation::getMethodComment
     */
    public function testGetDescription()
    {
        $documentation = new Documentation();

        $getMethodComment = $this->getObjectMethod($documentation, 'getMethodComment');
        $getDescription = $this->getObjectMethod($documentation, 'getDescription');

        $method = $this->createDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            "Test Description\non\nthree lines.",
            $getDescription($comment)
        );

        $method = $this->createDocBlockWithLineBreak();
        $comment = $getMethodComment($method);
        $this->assertSame(
            'This is a one line description',
            $getDescription($comment)
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $comment = $getMethodComment($method);
        $this->assertSame(
            '',
            $getDescription($comment)
        );

        $method = $this->createEmptyDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            '',
            $getDescription($comment)
        );
    }

    /**
     * @test
     * @covers ::getParameters
     * @uses \AyeAye\Api\Documentation::getMethodComment
     */
    public function testGetParameters()
    {
        $documentation = new Documentation();

        $getMethodComment = $this->getObjectMethod($documentation, 'getMethodComment');
        $getParameters = $this->getObjectMethod($documentation, 'getParameters');

        $method = $this->createDocBlock();
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
        $comment = $getMethodComment($method);
        $this->assertSame(
            $expected,
            $getParameters($comment)
        );

        $method = $this->createDocBlockWithLineBreak();
        $comment = $getMethodComment($method);
        $this->assertSame(
            [],
            $getParameters($comment)
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $comment = $getMethodComment($method);
        $this->assertSame(
            [],
            $getParameters($comment)
        );

        $method = $this->createEmptyDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            [],
            $getParameters($comment)
        );
    }

    /**
     * @test
     * @covers ::getReturnType()
     * @uses \AyeAye\Api\Documentation::getMethodComment
     */
    public function testGetReturnType()
    {
        $documentation = new Documentation();

        $getMethodComment = $this->getObjectMethod($documentation, 'getMethodComment');
        $getReturnType = $this->getObjectMethod($documentation, 'getReturnType');

        $method = $this->createDocBlock();
        $comment = $getMethodComment($method);

        $this->assertSame(
            ['string'],
            $getReturnType($comment, $method->getDeclaringClass())
        );

        $method = $this->createDocBlockWithLineBreak();
        $comment = $getMethodComment($method);
        $this->assertSame(
            ['self'],
            $getReturnType($comment, $method->getDeclaringClass())
        );

        $method = $this->createDocBlockNoDescriptionMultiReturnType();
        $comment = $getMethodComment($method);
        $this->assertSame(
            ['null', 'mixed'],
            $getReturnType($comment)
        );

        $method = $this->createEmptyDocBlock();
        $comment = $getMethodComment($method);
        $this->assertSame(
            [],
            $getReturnType($comment)
        );
    }
}
