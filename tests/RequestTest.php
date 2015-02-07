<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Request;

class RequestTest extends TestCase
{

    public function testDefaultRequest()
    {
        $request = new Request();

        $format = $request->getFormat();
        $this->assertSame(
            'json',
            $format,
            'Format is not json: ' . PHP_EOL . $format
        );

        $method = $request->getMethod();
        $this->assertSame(
            'GET',
            $method,
            'Method is not GET: ' . PHP_EOL . $method
        );

        $this->assertCount(
            0,
            $request->getParameters(),
            'No Parameters should have been defined'
        );

        $this->assertCount(
            0,
            $request->getRequestChain(),
            'There shouldn\'t be any elements in the request chain'
        );
    }

    /**
     * Test the Request classes ability to read headers
     */
    public function testParseHeader()
    {
        /** @var Request $request */
        $request = new Request();
        $headersSize = count($request->parseHeader());
        $this->assertEquals(
            0,
            $headersSize,
            'There shouldn\'t be any headers'
        );

        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '9001';
        $_SERVER['HTTP_NOT_A_REAL_HEADER'] = 'Not a real header';
        $_SERVER['NOT_A_HEADER'] = 'Not a header';

        $request = new Request();
        $headers = $request->parseHeader($_SERVER);
        $headersSize = count($headers);
        $this->assertEquals(
            3,
            $headersSize,
            'There should be 3 headers'
        );

        $this->assertEquals(
            $headers['Content-Type'],
            $_SERVER['CONTENT_TYPE'],
            'Content-Type should have been set to application/json'
        );

        $this->assertEquals(
            $headers['Content-Length'],
            $_SERVER['CONTENT_LENGTH'],
            'Content-Length should have been set to application/json'
        );

        $this->assertEquals(
            $headers['Not-A-Real-Header'],
            $_SERVER['HTTP_NOT_A_REAL_HEADER'],
            'Not-A-Real-Header should have been set to "Not a header"'
        );
    }


    public function testStringToObjectJson()
    {
        $json = '{"testArray" : [1, true], "testObject": {"string": "a string"}}';
        $request = new Request();
        $jsonObject = $request->stringToObject($json);

        $this->assertCount(
            2,
            $jsonObject->testArray,
            'testArray should contain 2 elements'
        );

        $this->assertSame(
            1,
            $jsonObject->testArray[0],
            'testArrays first element should be 1'
        );

        $this->assertSame(
            true,
            $jsonObject->testArray[1],
            'testArrays second element should be true'
        );

        $this->assertSame(
            'a string',
            $jsonObject->testObject->string,
            'testObject should contain the string "a string"'
        );
    }

    public function testStringToObjectXml()
    {
        $xml = '<data><testObject><string>a string</string></testObject></data>';
        $request = new Request();
        $xmlObject = $request->stringToObject($xml);

        $this->assertTrue(
            is_object($xmlObject),
            'testObject should be an object'
        );

        $this->assertEquals(
            'a string',
            $xmlObject->testObject->string,
            'testObject should contain the string "a string"'
        );
    }

    public function testStringToObjectPhp()
    {

        $php = 'O:8:"stdClass":2:{s:9:"testArray";a:2:{i:0;i:1;i:1;b:1;}s:10:"testObject";O:8:"stdClass":1:'
            .'{s:6:"string";s:8:"a string";}}';
        $request = new Request();
        $phpObject = $request->stringToObject($php);

        $this->assertCount(
            2,
            $phpObject->testArray,
            'testArray should contain 2 elements'
        );

        $this->assertSame(
            1,
            $phpObject->testArray[0],
            'testArrays first element should be 1'
        );

        $this->assertSame(
            true,
            $phpObject->testArray[1],
            'testArrays second element should be true'
        );

        $this->assertSame(
            'a string',
            $phpObject->testObject->string,
            'testObject should contain the string "a string"'
        );
    }

    public function testStringToStringObject()
    {
        $string = 'string';

        $request = new Request();
        $stringObejct = $request->stringToObject($string);

        $this->assertSame(
            $string,
            $stringObejct->text,
            'String should have been "string"'
        );
    }

    public function testGetMethod()
    {
        $request = new Request();
        $this->assertSame(
            Request::METHOD_GET,
            $request->getMethod(),
            'Default method should be GET'
        );

        $_SERVER['REQUEST_METHOD'] = Request::METHOD_DELETE; // Tested later
        $request = new Request(Request::METHOD_POST);
        $this->assertSame(
            Request::METHOD_POST,
            $request->getMethod(),
            'Method should be set to POST by constructor'
        );

        $request = new Request();
        $this->assertSame(
            Request::METHOD_DELETE,
            $request->getMethod(),
            'Method should be set to DELETE by $_SERVER'
        );
    }

    public function testGetParameter()
    {
        $request = new Request(
            null,
            '',
            ['true' => true, 'false' => false],
            '{"bodyString": "a string", "object": {"integer": 3}}'
        );

        $this->assertNull(
            $request->getParameter('this-parameter-not-set'),
            'The default value for an unknown value'
        );

        $this->assertSame(
            true,
            $request->getParameter('true'),
            'Test parameter "true" should be true'
        );

        $this->assertSame(
            false,
            $request->getParameter('false'),
            'Test parameter "false" should be false'
        );

        $this->assertSame(
            'a string',
            $request->getParameter('bodyString'),
            'bodyString should be "a string"'
        );


        $this->assertNull(
            $request->getParameter('this-parameter-not-set'),
            'The default value for an unknown value'
        );

    }

    public function testJsonSerializable()
    {
        $request = new Request(
            Request::METHOD_POST,
            '/test/path.xml',
            ['firstParameter' => '1'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            '{"secondParameter":2}'
        );

        $jsonObject = json_decode(json_encode($request));

        $this->assertSame(
            Request::METHOD_POST,
            $jsonObject->method,
            'Request method should be POST'
        );

        $this->assertSame(
            '/test/path.xml',
            $jsonObject->requestedUri,
            'Requested URI should be /test/path.xml'
        );

        $this->assertSame(
            Request::METHOD_POST,
            $jsonObject->method,
            'Request method should be POST'
        );

        $this->assertSame(
            '1',
            $jsonObject->parameters->firstParameter,
            'First parameter should be 1'
        );

        $this->assertSame(
            2,
            $jsonObject->parameters->secondParameter,
            'Second parameter should be 2'
        );

        // Lets check the server variable is read too
        $_SERVER['REQUEST_URI'] = '/test/path.xml?parameter=value';

        $request = new Request();
        $jsonObject = json_decode(json_encode($request));
        $this->assertSame(
            $_SERVER['REQUEST_URI'],
            $jsonObject->requestedUri,
            "Request URI should be {$_SERVER['REQUEST_URI']}"
        );

    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Add parameter: parameter name must be scalar
     * @expectedExceptionCode    0
     */
    public function testAddParameterException()
    {
        $request = new Request();
        $request->setParameter([], []);
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Add parameters parameter newParameters can not be scalar
     * @expectedExceptionCode    0
     */
    public function testAddParametersException()
    {
        $request = new Request();
        $request->setParameters(true);
    }

    public function testAddParameterFail()
    {
        $request = new Request();
        $this->assertTrue(
            $request->setParameter('name', 'value'),
            "Add parameter should have returned true, it didn't"
        );

        $this->assertSame(
            'value',
            $request->getParameter('name'),
            "Parameter should have been 'value'"
        );
    }

    public function testReadBodyDodgily()
    {

        require_once 'TestData/http_get_request_body.php';

        $request = new Request();
        $this->assertSame(
            true,
            $request->getParameter('hackedJson'),
            'Failed to utilise hacked http_get_request_body... I\'m not sure how I feel about that'
        );
    }

    public function testGetFormatFromUri()
    {
        $request = new Request();

        $uri = '/test/file.php';
        $this->assertSame(
            'php',
            $request->getFormatFromUri($uri),
            'Format should be php'
        );

        $uri = '/test/file.json';
        $this->assertSame(
            'json',
            $request->getFormatFromUri($uri),
            'Format should be json'
        );

        $uri = '/test/file.json?parameters=true';
        $this->assertSame(
            'json',
            $request->getFormatFromUri($uri),
            'Format should be json'
        );
    }
}
