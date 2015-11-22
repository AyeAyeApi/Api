<?php
/**
 * ResponseTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\TestsOld;

use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use AyeAye\Api\TestsOld\TestData\GeneratorController;
use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer;
use AyeAye\Formatter\Writer\Json;

/**
 * Class ResponseTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
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
        $generator = function() {
            yield 'data' => 'data';
            yield 'string' => 'string';
            yield 'integer' => 42;
        };

        $response = new Response();

        $this->assertSame(
            $response,
            $response->setBodyData(
                $generator()
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
     * @covers ::setWriterFactory
     * @uses \AyeAye\Formatter\WriterFactory
     */
    public function testWriterFactory()
    {
        $expectedWriterFactory = new WriterFactory([]);
        $response = new Response();

        $this->assertSame(
            $response,
            $response->setWriterFactory($expectedWriterFactory)
        );

        $actualWriterFactory = $this->getObjectAttribute($response, 'writerFactory');

        $this->assertSame(
            $expectedWriterFactory,
            $actualWriterFactory
        );
    }

    /**
     * @test
     * @covers ::setWriter
     * @uses \AyeAye\Formatter\Writer\Json
     */
    public function testWriter()
    {
        $expectedWriter = new Json();
        $response = new Response();

        $this->assertSame(
            $response,
            $response->setWriter($expectedWriter)
        );

        $actualWriter = $this->getObjectAttribute($response, 'writer');

        $this->assertSame(
            $expectedWriter,
            $actualWriter
        );
    }

    /**
     * @test
     * @covers ::prepareResponse
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response::setWriterFactory
     * @uses \AyeAye\Api\Response::setRequest
     * @uses \AyeAye\Api\Response::getBody
     * @uses \AyeAye\Api\Response::setBodyData
     */
    public function testPrepareResponse()
    {
        $response = new Response();
        $writers = [
            'testWriter'
        ];
        $data = 'data';
        $expectedBody = [
            'data' => $data
        ];
        $response->setBodyData($data);

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('\AyeAye\Api\Request');
        $request
            ->expects($this->once())
            ->method('getFormats')
            ->with()
            ->will($this->returnValue($writers));

        $writer = $this->getMock('\AyeAye\Formatter\Writer');
        $writer
            ->expects($this->once())
            ->method('format')
            ->with($expectedBody, 'response')
            ->will($this->returnValue(json_encode($expectedBody)));

        /** @var WriterFactory|\PHPUnit_Framework_MockObject_MockObject $writerFactory */
        $writerFactory = $this
            ->getMockBuilder('\AyeAye\Formatter\WriterFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $writerFactory
            ->expects($this->once())
            ->method('getWriterFor')
            ->with($writers)
            ->will($this->returnValue($writer));

        $response
            ->setWriterFactory($writerFactory)
            ->setRequest($request);

        $this->assertSame(
            $response,
            $response->prepareResponse()
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedBody),
            $this->getObjectAttribute($response, 'preparedResponse')
        );

        return $response;
    }

    /**
     * @test
     * @runInSeparateProcess
     * @depends testPrepareResponse
     * @param Response $response
     * @covers ::respond
     * @uses \AyeAye\Api\Request
     */
    public function testRespond(Response $response)
    {
        /** @var Writer|\PHPUnit_Framework_MockObject_MockObject $writer */
        $writer = $this->getMock('\AyeAye\Formatter\Writer');
        $writer
            ->expects($this->exactly(2))
            ->method('getContentType')
            ->with()
            ->will($this->returnValue(''));

        /** @var Status|\PHPUnit_Framework_MockObject_MockObject $status */
        $status = $this->getMockBuilder('\AyeAye\Api\Status')
                       ->disableOriginalConstructor()
                       ->getMock();
        $status
            ->expects($this->once())
            ->method('getHttpHeader')
            ->with()
            ->will($this->returnValue(null));

        $response->setWriter($writer);

        ob_start();
        $this->assertSame(
            $response,
            $response->respond()
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode(['data'=>'data']),
            ob_get_clean()
        );


        ob_start();
        $response->setStatus($status);
        $this->assertSame(
            $response,
            $response->respond()
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode(['data'=>'data']),
            ob_get_clean()
        );
    }

    /**
     * @test
     * @runInSeparateProcess
     * @covers ::respond
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response::setWriterFactory
     * @uses \AyeAye\Api\Response::setRequest
     * @uses \AyeAye\Api\Response::getBody
     * @uses \AyeAye\Api\Response::setBodyData
     * @uses \AyeAye\Api\Response::prepareResponse
     */
    public function testRespondFull()
    {
        $response = new Response();
        $writers = [
            'testWriter'
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
            ->will($this->returnValue($writers));

        $writer = $this->getMock('\AyeAye\Formatter\Writer');
        $writer
            ->expects($this->once())
            ->method('format')
            ->with($expectedBody, 'response')
            ->will($this->returnValue(json_encode($expectedBody)));
        $writer
            ->expects($this->once())
            ->method('getContentType')
            ->with()
            ->will($this->returnValue(''));

        /** @var WriterFactory|\PHPUnit_Framework_MockObject_MockObject $writerFactory */
        $writerFactory =
            $this->getMockBuilder('\AyeAye\Formatter\WriterFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $writerFactory
            ->expects($this->once())
            ->method('getWriterFor')
            ->with($writers)
            ->will($this->returnValue($writer));

        /** @var Status|\PHPUnit_Framework_MockObject_MockObject $status */
        $status = $this->getMockBuilder('\AyeAye\Api\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status
            ->expects($this->once())
            ->method('getHttpHeader')
            ->with()
            ->will($this->returnValue(null));

        $response
            ->setWriterFactory($writerFactory)
            ->setRequest($request)
            ->setStatus($status);

        ob_start();
        $this->assertSame(
            $response,
            $response->respond()
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode(['data'=>'data']),
            ob_get_clean()
        );
    }
}
