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

        try {
            throw new Exception($testPublicMessage, $testCode, $testMessage);
        } catch (Exception $e) {

            $message = $e->getMessage();
            $this->assertTrue(
                $message === $testMessage,
                "Exception messsage was not $testMessage: " . PHP_EOL . $message
            );

            $code = $e->getCode();
            $this->assertTrue(
                $code === $testCode,
                "Exception code was not $testCode: " . PHP_EOL . $message
            );

            $publicMessage = $e->getPublicMessage();
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: " . PHP_EOL . $publicMessage
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
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: " . PHP_EOL . $publicMessage
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
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: " . PHP_EOL . $publicMessage
            );

        }
    }

    public function testCodeWithSystemMessage()
    {
        $testPublicMessage = "I'm a teapot";
        $systemMessage = 'Teapot Exception triggered';

        try {
            throw new Exception(418, $systemMessage);
        } catch (Exception $e) {

            $this->assertSame(
                $testPublicMessage, $e->getPublicMessage(),
                "Exception public message incorrect"
            );

            $this->assertSame(
                $systemMessage, $e->getMessage(),
                "Exception system message incorrect"
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

        $count = count($object);
        $this->assertTrue(
            $count === 3,
            'There should only be 3 items in the array, there were: ' . PHP_EOL . $count
        );

        $publicMessage = $object['message'];
        $this->assertTrue(
            $publicMessage === $testPublicMessage,
            "Exception messsage was not $testMessage: " . PHP_EOL . $publicMessage
        );

        $code = $object['code'];
        $this->assertTrue(
            $code === $testCode,
            "Exception code was not $testCode: " . PHP_EOL . $code
        );

    }

}
 