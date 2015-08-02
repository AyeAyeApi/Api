<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\DocumentedController;
use AyeAye\Api\Tests\TestData\GeneratorController;
use AyeAye\Formatter\FormatFactory;
use AyeAye\Formatter\Formats\Json;

/**
 * Class ResponseTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\Response
 */
class ResponseTest extends TestCase
{

    /**
     * @test
     * @covers ::getStatus
     * @covers ::setStatus
     * @uses \AyeAye\Api\Status
     */
    public function testStatus()
    {
        $response = new Response();
        $status = new Status();

        $this->assertNull(
            $response->getStatus()
        );
        $this->assertSame(
            $response,
            $response->setStatus($status)
        );
        $this->assertSame(
            $status,
            $response->getStatus()
        );
    }

    /**
     * @test
     * @covers ::setStatusCode
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Response::setStatus
     * @uses \AyeAye\Api\Response::getStatus
     */
    public function testStatusCode()
    {
        $code = 418;
        $response = new Response();

        $this->assertNull(
            $response->getStatus()
        );
        $this->assertSame(
            $response,
            $response->setStatusCode(418)
        );
        $this->assertInstanceOf(
            '\AyeAye\Api\Status',
            $response->getStatus()
        );
        $this->assertSame(
            $code,
            $response->getStatus()->getCode()
        );
    }

    /**
     * @test
     * @covers ::setRequest
     * @covers ::getRequest
     * @uses \AyeAye\Api\Request
     */
    public function testRequest()
    {
        $response = new Response();
        $this->assertNull(
            $response->getRequest()
        );

        $request = new Request();
        $this->assertSame(
            $response,
            $response->setRequest($request)
        );

        $this->assertSame(
            $request,
            $response->getRequest()
        );
    }

    /**
     * @test
     * @covers ::setBodyData
     * @covers ::getBody
     */
    public function testBody()
    {
        $response = new Response();
        $this->assertEmpty(
            $response->getBody()
        );

        $object = new \stdClass(); // Good for tracking reference
        $this->assertSame(
            $response,
            $response->setBodyData($object)
        );
        $this->assertArrayHasKey(
            'data',
            $response->getBody()
        );
        $this->assertSame(
            $object,
            $response->getBody()['data']
        );
    }

    /**
     * @test
     * @covers ::setBodyData
     * @covers ::getData
     */
    public function testData()
    {
        $response = new Response();
        $this->assertNull(
            $response->getData()
        );

        $object = new \stdClass(); // Good for tracking reference

        $this->assertSame(
            $response,
            $response->setBodyData($object)
        );
        $this->assertSame(
            $object,
            $response->getData()
        );
    }

    /**
     * @test
     * @covers ::setBodyData
     * @covers ::getBody
     * @requires PHP 5.5
     */
    public function testBodyGenerator()
    {
        $controller = new GeneratorController();
        $response = new Response();

        $this->assertSame(
            $response,
            $response->setBodyData(
                $controller->getGeneratorEndpoint()
            )
        );

        $this->assertSame(
            [
                'data' => 'data',
                'string' => 'string',
                'integer' => 42,
            ],
            $response->getBody()
        );
    }

    /**
     * @test
     * @covers ::setFormatFactory
     * @uses \AyeAye\Formatter\FormatFactory
     */
    public function testFormatFactory()
    {
        $expectedFormatFactory = new FormatFactory([]);
        $response = new Response();

        $this->assertSame(
            $response,
            $response->setFormatFactory($expectedFormatFactory)
        );

        $actualFormatFactory = $this->getObjectAttribute($response, 'formatFactory');

        $this->assertSame(
            $expectedFormatFactory,
            $actualFormatFactory
        );
    }

    /**
     * @test
     * @covers ::setFormatter
     * @uses \AyeAye\Formatter\Formats\Json
     */
    public function testFormatter()
    {
        $expectedFormatter = new Json();
        $response = new Response();

        $this->assertSame(
            $response,
            $response->setFormatter($expectedFormatter)
        );

        $actualFormatter = $this->getObjectAttribute($response, 'formatter');

        $this->assertSame(
            $expectedFormatter,
            $actualFormatter
        );
    }

    /**
     * @test
     * @covers ::prepareResponse
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response::setFormatFactory
     * @uses \AyeAye\Api\Response::setRequest
     * @uses \AyeAye\Api\Response::getBody
     * @uses \AyeAye\Api\Response::setBodyData
     */
    public function testPrepareResponse()
    {
        $response = new Response();
        $formats = [
            'testFormat'
        ];
        $data = 'data';
        $expectedBody = [
            'data' => $data
        ];
        $response->setBodyData($data);

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\AyeAye\Api\Request');
        $request->expects($this->once())
            ->method('getFormats')
            ->with()
            ->will($this->returnValue($formats));

        $formatter = $this->getMock('\AyeAye\Formatter\Formatter');
        $formatter
            ->expects($this->once())
            ->method('getHeader')
            ->with()
            ->will($this->returnValue(''));
        $formatter
            ->expects($this->once())
            ->method('getFooter')
            ->with()
            ->will($this->returnValue(''));
        $formatter
            ->expects($this->once())
            ->method('format')
            ->with($expectedBody, 'response')
            ->will($this->returnValue(json_encode($expectedBody)));

        /** @var FormatFactory|\PHPUnit_Framework_MockObject_MockObject $formatFactory */
        $formatFactory =
            $this->getMockBuilder('\AyeAye\Formatter\FormatFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $formatFactory
            ->expects($this->once())
            ->method('getFormatterFor')
            ->with($formats)
            ->will($this->returnValue($formatter));

        $response
            ->setFormatFactory($formatFactory)
            ->setRequest($request);

        $this->assertSame(
            $response,
            $response->prepareResponse()
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedBody),
            $this->getObjectAttribute($response, 'preparedResponse')
        );

    }

}