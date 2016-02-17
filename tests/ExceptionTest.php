<?php
/**
 * ExceptionTest.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Exception;

/**
 * Class ExceptionTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass \AyeAye\Api\Exception
 */
class ExceptionTest extends TestCase
{

    /**
     * @test
     * @covers ::__construct
     */
    public function testContructStandard()
    {
        $publicMessage = 'Public Message';
        $code = 418;
        $systemMessage = 'System Message';
        $previousException = new \Exception();
        $exception = new Exception(
            $publicMessage,
            $code,
            $systemMessage,
            $previousException
        );

        $this->assertSame(
            $publicMessage,
            $this->getObjectAttribute($exception, 'publicMessage')
        );
        $this->assertSame(
            $systemMessage,
            $this->getObjectAttribute($exception, 'message')
        );
        $this->assertSame(
            $code,
            $this->getObjectAttribute($exception, 'code')
        );
        $this->assertSame(
            $previousException,
            $this->getObjectAttribute($exception, 'previous')
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @uses AyeAye\Api\Status
     */
    public function testContructShifted()
    {
        $code = 418;
        $systemMessage = 'System Message';
        $previousException = new \Exception();
        $exception = new Exception(
            $code,
            $systemMessage,
            $previousException
        );

        $this->assertSame(
            'I\'m a teapot',
            $this->getObjectAttribute($exception, 'publicMessage')
        );
        $this->assertSame(
            $systemMessage,
            $this->getObjectAttribute($exception, 'message')
        );
        $this->assertSame(
            $code,
            $this->getObjectAttribute($exception, 'code')
        );
        $this->assertSame(
            $previousException,
            $this->getObjectAttribute($exception, 'previous')
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @uses AyeAye\Api\Status
     */
    public function testContructSimplified()
    {
        $exception = new Exception(999);
        $this->assertSame(
            'Internal Server Error',
            $this->getObjectAttribute($exception, 'publicMessage')
        );
        $this->assertSame(
            'Internal Server Error',
            $this->getObjectAttribute($exception, 'message')
        );
        $this->assertSame(
            999,
            $this->getObjectAttribute($exception, 'code')
        );
        $this->assertNull(
            $this->getObjectAttribute($exception, 'previous')
        );
    }

    /**
     * @test
     * @covers ::getPublicMessage
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Exception::__construct
     */
    public function testGetPublicMessage()
    {
        $exception = new Exception();
        $this->assertSame(
            'Internal Server Error',
            $exception->getPublicMessage()
        );

        $exception = new Exception('Test');
        $this->assertSame(
            'Test',
            $exception->getPublicMessage()
        );
    }

    /**
     * @test
     * @covers ::jsonSerialize
     * @uses AyeAye\Api\Exception::__construct
     * @uses AyeAye\Api\Exception::getPublicMessage
     */
    public function testJsonSerialize()
    {
        $publicMessage = 'Public Message';
        $code = 418;
        $systemMessage = 'System Message';
        $previousException = new \Exception();
        $exception = new Exception(
            $publicMessage,
            $code,
            $systemMessage,
            $previousException
        );

        $this->assertSame(
            [
                'message' => $publicMessage,
                'code' => $code
            ],
            $exception->jsonSerialize()
        );

        $newPublicMessage = 'Public Message';
        $newCode = 418;
        $newSystemMessage = 'System Message';
        $newException = new Exception(
            $newPublicMessage,
            $newCode,
            $newSystemMessage,
            $exception
        );

        $this->assertSame(
            [
                'message' => $newPublicMessage,
                'code' => $newCode,
                'previous' => [
                    'message' => $publicMessage,
                    'code' => $code
                ]
            ],
            $newException->jsonSerialize()
        );
    }
}
