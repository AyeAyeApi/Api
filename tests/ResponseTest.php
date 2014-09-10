<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Request;
use Gisleburt\Api\Response;
use Gisleburt\Api\Status;
use Gisleburt\Formatter\Formats\Json;
use Gisleburt\Formatter\Formats\Xml;
use Gisleburt\Formatter\FormatFactory;

class ResponseTest extends TestCase
{

    public function testSetData()
    {
        $testData = 'TestData';
        $response = new Response();
        $response->setData($testData);

        $data = $response->getData();
        $this->assertTrue(
            $data === $testData,
            'Data did not match test data' . PHP_EOL . $data
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

        $status = $response->getStatus();
        $this->assertTrue(
            $status->getCode() === $testStatus->getCode(),
            'Status did not match test status' . PHP_EOL . $status->getCode()
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

        $status = $response->getStatus();
        $this->assertTrue(
            $status->getCode() === $testStatusCode,
            'Status did not match test status' . PHP_EOL . $status->getCode()
        );
    }

    /**
     * Test that an exception is thrown when there is no Format Factory
     *
     * @expectedException        \Exception
     * @expectedExceptionMessage Format factory not set
     */
    public function testSetFormatWithoutFactory()
    {
        $testFormat = 'xml';
        $response = new Response;
        $response->setFormat($testFormat);
    }

    public function testSetFormatWithFactory()
    {

        $testFormat = 'xml';
        $formatFactory = new FormatFactory([
            $testFormat => $this->getMock('\Gisleburt\Formatter\Formats\Xml')
        ]);

        $response = new Response;
        $response->setFormatFactory($formatFactory);
        $response->setFormat($testFormat);

        $format = $response->getFormat();
        $this->assertTrue(
            $format instanceof Xml,
            'Format returned was not of type format: ' . PHP_EOL . get_class($format)
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
            ['testParameter' => 'value']
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

        $this->assertTrue(
            $responseObject->status->code === '418',
            'The response object should contain status code 418, is actually: ' . PHP_EOL . $responseObject->status->code
        );

        $this->assertTrue(
            $responseObject->data->string === 'string',
            'The response object should contain the string "string", is actually: ' . PHP_EOL . $responseObject->data->string
        );

        $this->assertTrue(
            $responseObject->request->requestedUri === '/test/path',
            'The response object should contain the string "/test/path", is actually: ' . PHP_EOL . $responseObject->request->requestedUri
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
            . '"status":{"code":200,"message":"OK"},'
            . '"request":{"method":"GET","requestedUri":"test.json","parameters":{"hackedJson":true}},'
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

        $this->assertTrue(
            $responseData === $expectedXml,
            "Response data not correct Expected:\n$expectedXml\nGot:\n$responseData"
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
            . '<status><array><code>200</code><message>OK</message></array></status>'
            . '<request><array><method>GET</method><requestedUri>test.xml</requestedUri><parameters><hackedJson>true</hackedJson></parameters></array></request>'
            . '<data><childObject><property>value</property></childObject><childArray><_0>element1</_0><_1>element2</_1></childArray></data>' .
            '</response>';

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

        $this->assertTrue(
            $responseData === $expectedXml,
            "Response data not correct Expected:\n$expectedXml\nGot:\n$responseData"
        );
    }


}
 