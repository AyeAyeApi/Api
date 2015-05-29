<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use AyeAye\Formatter\Formats\Json;
use AyeAye\Formatter\Formats\Xml;
use AyeAye\Formatter\FormatFactory;

class ResponseTest extends TestCase
{

    public function testSetData()
    {
        $testData = 'TestData';
        $response = new Response();
        $response->setData($testData);

        $this->assertSame(
            $testData,
            $response->getData()
        );
    }

    /**
     * Test setting the status with a status object
     */
    public function testSetStatus()
    {
        $testStatus = new Status(418);
        $response = new Response();
        $response->setStatus($testStatus);

        $this->assertSame(
            $testStatus->getCode(),
            $response->getStatus()->getCode()
        );
    }

    /**
     * Test setting the status with a status object
     */
    public function testSetStatusCode()
    {
        $testStatusCode = 418;
        $response = new Response();
        $response->setStatusCode($testStatusCode);

        $this->assertSame(
            $testStatusCode,
            $response->getStatus()->getCode()
        );
    }

    public function testSetRequest()
    {
        $request = new Request();
        $response = new Response();
        $response->setFormatFactory(
            new FormatFactory([
                'json' => new Json()
            ])
        );
        $response->setRequest($request);
        $response->getRequest();
    }

    public function testJsonSerializable()
    {
        $testData = new \stdClass();
        $testData->string = 'string';
        $testStatusCode = '418';
        $testRequest = new Request(
            Request::METHOD_POST,
            '/test/path',
            [
                'testParameter' => 'value'
            ]
        );

        $response = new Response();
        $response->setFormatFactory(
            new FormatFactory([
                'json' => new Json()
            ])
        );
        $response->setData($testData);
        $response->setStatusCode($testStatusCode);
        $response->setRequest($testRequest);

        $responseObject = json_decode(json_encode($response));

        $this->assertSame(
            'string',
            $responseObject->data->string
        );

    }

    /**
     * @runInSeparateProcess
     */
    public function testXmlRespond()
    {
        $complexObject = (object)[
            'childObject' => (object)[
                    'property' => 'value'
                ],
            'childArray' => [
                'element1',
                'element2'
            ]
        ];
        $expectedXml =
            '<?xml version="1.0" encoding="UTF-8" ?>'
            . '<response>'
            . '<data>'
            . '<childObject><property>value</property></childObject>'
            . '<childArray><_0>element1</_0><_1>element2</_1></childArray>'
            . '</data>'
            . '</response>';

        $request = new Request(
            Request::METHOD_GET,
            'test.xml'
        );
        $response = new Response();
        $response->setFormatFactory(
            new FormatFactory([
                'xml' => new Xml()
            ])
        );
        $response->setRequest($request);
        $response->setStatus(new Status());
        $response->setData($complexObject);

        ob_start();
        $response->respond();
        $responseData = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            $responseData,
            $expectedXml
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonRespond()
    {
        $complexObject = (object)[
            'childObject' => (object)[
                    'property' => 'value'
                ],
            'childArray' => [
                'element1',
                'element2'
            ]
        ];
        $expectedXml =
            '{'
            . '"data":{"childObject":{"property":"value"},"childArray":["element1","element2"]}'
            . '}';

        $request = new Request(
            Request::METHOD_GET,
            'test.json'
        );
        $response = new Response();
        $response->setFormatFactory(
            new FormatFactory([
                'json' => new Json()
            ])
        );
        $response->setRequest($request);
        $response->setStatus(new Status());
        $response->setData($complexObject);

        ob_start();
        $response->respond();
        $responseData = ob_get_contents();
        ob_end_clean();

        $this->assertSame(
            $responseData,
            $expectedXml
        );
    }
}
