<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Exception;

class ExceptionTest extends TestCase
{

    /**
     * Test that general Exception behavior is maintained
     * @throws \AyeAye\Api\Exception
     *
     * @expectedException        \Exception
     * @expectedExceptionMessage Basic Exception Message
     * @expectedExceptionCode    500
     */
    public function testThrowException()
    {
        throw new Exception('Basic Exception Message', 500);
    }

    public function testPublicMessage()
    {

        $testMessage = 'Message';
        $testCode = 101;
        $testPublicMessage = 'Public Message';
        $previousException = new \Exception('Previous exception');

        try {
            throw new Exception($testPublicMessage, $testCode, $testMessage, $previousException);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $this->assertSame(
                $testMessage,
                $message
            );

            $code = $e->getCode();
            $this->assertSame(
                $testCode,
                $code
            );

            $publicMessage = $e->getPublicMessage();
            $this->assertSame(
                $testPublicMessage,
                $publicMessage
            );

            $previous = $e->getPrevious();

            $this->assertTrue(
                $previous instanceof \Exception
            );

            $this->assertSame(
                $previousException->getMessage(),
                $previous->getMessage()
            );
        }
    }

    public function testDefaultPublicMessage()
    {
        $testCode = 9001;
        $testPublicMessage = 'Internal Server Error';

        try {
            throw new Exception($testCode);
        } catch (Exception $e) {
            $publicMessage = $e->getPublicMessage();
            $this->assertSame(
                $testPublicMessage,
                $publicMessage
            );
        }
    }

    public function testCodeStatusMessage()
    {
        $testPublicMessage = "I'm a teapot";

        try {
            throw new Exception(418);
        } catch (Exception $e) {
            $publicMessage = $e->getPublicMessage();
            $this->assertSame(
                $testPublicMessage,
                $publicMessage
            );

        }
    }

    public function testCodeWithSystemMessage()
    {
        $testPublicMessage = "I'm a teapot";
        $systemMessage = 'Teapot Exception triggered';
        $previousException = new \Exception('Previous exception');

        try {
            throw new Exception(418, $systemMessage, $previousException);
        } catch (Exception $e) {
            $this->assertSame(
                $testPublicMessage,
                $e->getPublicMessage()
            );

            $this->assertSame(
                $systemMessage,
                $e->getMessage()
            );

            $previous = $e->getPrevious();

            $this->assertTrue(
                $previous instanceof \Exception
            );

            $this->assertSame(
                $previousException->getMessage(),
                $previous->getMessage()
            );

        }
    }

    public function testJsonSerialization()
    {

        $testMessage = 'Message';
        $testCode = 101;
        $testPublicMessage = 'Public Message';

        $exception = new Exception($testPublicMessage, $testCode, $testMessage);

        $json = json_encode($exception);

        $object = json_decode($json, true);

        $this->assertCount(
            2,
            $object
        );

        $publicMessage = $object['message'];
        $this->assertSame(
            $testPublicMessage,
            $publicMessage
        );

        $code = $object['code'];
        $this->assertSame(
            $testCode,
            $code
        );

    }

    public function testExceptionChaining()
    {
        $testMessage = 'Message';
        $testCode = 101;
        $testPublicMessage = 'Public Message';
        $previousException = new \Exception('Previous exception');

        $exception = new Exception($testPublicMessage, $testCode, $testMessage, $previousException);

        $json = json_encode($exception);

        $object = json_decode($json, true);

        $this->assertCount(
            2,
            $object
        );

        $newException = new Exception($testPublicMessage, $testCode, $testMessage, $exception);

        $json = json_encode($newException);

        $object = json_decode($json, true);

        $this->assertCount(
            3,
            $object
        );
    }
}
