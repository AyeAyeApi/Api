<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Exception;

class ExceptionTest extends TestCase {

    /**
     * Test that general Exception behavior is maintained
     * @throws \Gisleburt\Api\Exception
     *
     * @expectedException        \Exception
     * @expectedExceptionMessage Basic Exception Message
     * @expectedExceptionCode    500
     */
    public function testThrowException() {
        throw new Exception('Basic Exception Message', 500);
    }

    public function testPublicMessage() {

        $testMessage = 'Message';
        $testCode = 101;
        $testPublicMessage = 'Public Message';

        try {
            throw new Exception($testMessage, $testCode, $testPublicMessage);
        }
        catch(Exception $e) {

            $message = $e->getMessage();
            $this->assertTrue(
                $message === $testMessage,
                "Exception messsage was not $testMessage: ".PHP_EOL.$message
            );

            $code = $e->getCode();
            $this->assertTrue(
                $code === $testCode,
                "Exception code was not $testCode: ".PHP_EOL.$message
            );

            $publicMessage = $e->getPublicMessage();
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: ".PHP_EOL.$publicMessage
            );
        }
    }

    public function testDefaultPublicMessage() {
        $testCode = 9001;
        $testPublicMessage = 'Internal Server Error';

        try {
            throw new Exception('Test', $testCode);
        }
        catch(Exception $e) {

            $publicMessage = $e->getPublicMessage();
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: ".PHP_EOL.$publicMessage
            );
        }
    }

    public function testCodeStatusMessage() {

        $testPublicMessage = "I'm a teapot";

        try {
            throw new Exception('Test', 418);
        }
        catch(Exception $e) {

            $publicMessage = $e->getPublicMessage();
            $this->assertTrue(
                $publicMessage == $testPublicMessage,
                "Exception public message was not $testPublicMessage: ".PHP_EOL.$publicMessage
            );

        }

    }

    public function testJsonSerialization() {

        $testMessage = 'Message';
        $testCode = 101;
        $testPublicMessage = 'Public Message';

        $exception = new Exception($testMessage, $testCode, $testPublicMessage);

        $json = json_encode($exception);

        $object = json_decode($json, true);

        $count = count($object);
        $this->assertTrue(
            $count === 3,
            'There should only be 3 items in the array, there were: '.PHP_EOL.$count
        );

        $publicMessage = $object['message'];
        $this->assertTrue(
            $publicMessage === $testMessage,
            "Exception messsage was not $testMessage: ".PHP_EOL.$publicMessage
        );

        $code = $object['code'];
        $this->assertTrue(
            $code === $testCode,
            "Exception code was not $testCode: ".PHP_EOL.$code
        );

    }

}
 