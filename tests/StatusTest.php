<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Exception;
use AyeAye\Api\Status;

class StatusTest extends TestCase
{

    /**
     * Test that general Exception behavior is maintained
     * @throws \AyeAye\Api\Exception
     *
     * @expectedException        Exception
     * @expectedExceptionMessage Status '9001' does not exist
     * @expectedExceptionCode    0
     */
    public function testConstructThrowException()
    {
        new Status(9001);
    }


    public function testJsonSerialisable()
    {
        $status = new Status(418);
        $statusObject = json_decode(json_encode($status));

        $this->assertSame(
            418,
            $statusObject->code
        );

        $this->assertSame(
            'I\'m a teapot',
            $statusObject->message
        );
    }

    /**
     * Headers can not be tested in CLI since PHP v5.2
     */
    public function testHttpHeader()
    {
        $status = new Status();
        $header = $status->getHttpHeader();

        $this->assertTrue(
            'HTTP/1.1 200 OK' === $header,
            'Default Header not correct'
        );

        $status = new Status(418);
        $header = $status->getHttpHeader();
        $this->assertSame(
            'HTTP/1.1 418 I\'m a teapot',
            $header
        );
    }
}
