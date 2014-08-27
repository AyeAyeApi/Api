<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Request;

class RequestTest extends TestCase {

    public function testDefaultRequest() {
        $request = new Request();

        $format = $request->getFormat();
        $this->assertTrue(
            $format === 'json',
            'Format is not json: '.PHP_EOL.$format
        );

        $method = $request->getMethod();
        $this->assertTrue(
            $method === 'GET',
            'Method is not GET: '.PHP_EOL.$method
        );

        $numParameters = count($request->getParameters());
        $this->assertTrue(
            $numParameters === 0,
            'No Parameters should have been defined, there are: '.PHP_EOL.$numParameters
        );

        $requestChainSize = count($request->getRequestChain());
        $this->assertTrue(
            $requestChainSize === 0,
            'There shouldn\'t be any elements in the request chain, there are: '.PHP_EOL.$requestChainSize
        );
    }

    /**
     * Test the Request classes ability to read headers
     */
    public function testParseHeader() {
        /** @var Request $request */
        $request = new Request();
        $headersSize = count($request->parseHeader());
        $this->assertTrue(
            $headersSize == 0,
            'There shouldn\'t be any headers, there are: '.PHP_EOL.$headersSize
        );

        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['CONTENT_LENGTH'] = '9001';
        $_SERVER['HTTP_NOT_A_REAL_HEADER'] = 'Not a real header';
        $_SERVER['NOT_A_HEADER'] = 'Not a header';

        $request = new Request();
        $headers = $request->parseHeader($_SERVER);
        $headersSize = count($headers);
        $this->assertTrue(
            $headersSize == 3,
            'There should be 3 headers, there are: '.PHP_EOL.$headersSize
        );

        $this->assertTrue(
            $headers['Content-Type'] === $_SERVER['CONTENT_TYPE'],
            'Content-Type should have been set to application/json, it was: '.PHP_EOL.$headers['Content-Type']
        );

        $this->assertTrue(
            $headers['Content-Length'] === $_SERVER['CONTENT_LENGTH'],
            'Content-Length should have been set to application/json, it was: '.PHP_EOL.$headers['Content-Length']
        );

        $this->assertTrue(
            $headers['Not-A-Real-Header'] === $_SERVER['HTTP_NOT_A_REAL_HEADER'],
            'Content-Length should have been set to application/json, it was: '.PHP_EOL.$headers['Content-Length']
        );
    }


    public function testStringToObjectJson() {
        $json = '{"testArray" : [1, true], "testObject": {"string": "a string"}}';
        $request = new Request();
        $jsonObject = $request->stringToObject($json);

        $testArraySize = count($jsonObject->testArray);
        $this->assertTrue(
            $testArraySize === 2,
            'testArray should contain 2 elements, it contains: '.PHP_EOL.$testArraySize
        );

        $this->assertTrue(
            $jsonObject->testArray[0] === 1,
            'testArrays first element should be 1, is actually'.PHP_EOL.$jsonObject->testArray[0]
        );

        $this->assertTrue(
            $jsonObject->testArray[1] === true,
            'testArrays second element should be true, is actually'.PHP_EOL.$jsonObject->testArray[1]
        );

        $this->assertTrue(
            $jsonObject->testObject->string === "a string",
            'testObject should contain the string "a string", is actually'.PHP_EOL.$jsonObject->testObject->string
        );
    }

    public function testStringToObjectXml() {
        $xml = '<data><testObject><string>a string</string></testObject></data>';
        $request = new Request();
        $xmlObject = $request->stringToObject($xml);

        $this->assertTrue(
            is_object($xmlObject),
            'testObject should be an object'
        );

        $this->assertTrue(
            $xmlObject->testObject->string == "a string",
            'testObject should contain the string "a string", is actually'.PHP_EOL.$xmlObject->testObject->string
        );
    }

    public function testStringToObjectPhp() {

        $php = 'O:8:"stdClass":2:{s:9:"testArray";a:2:{i:0;i:1;i:1;b:1;}s:10:"testObject";O:8:"stdClass":1:{s:6:"string";s:8:"a string";}}';
        $request = new Request();
        $phpObject = $request->stringToObject($php);

        $testArraySize = count($phpObject->testArray);
        $this->assertTrue(
            $testArraySize === 2,
            'testArray should contain 2 elements, it contains: '.PHP_EOL.$testArraySize
        );

        $this->assertTrue(
            $phpObject->testArray[0] === 1,
            'testArrays first element should be 1, is actually'.PHP_EOL.$phpObject->testArray[0]
        );

        $this->assertTrue(
            $phpObject->testArray[1] === true,
            'testArrays second element should be true, is actually'.PHP_EOL.$phpObject->testArray[1]
        );

        $this->assertTrue(
            $phpObject->testObject->string === "a string",
            'testObject should contain the string "a string", is actually'.PHP_EOL.$phpObject->testObject->string
        );
    }

    public function testStringToStringObject() {
        $string = 'string';

        $request = new Request();
        $stringObejct = $request->stringToObject($string);

        $this->assertTrue(
            $stringObejct->text === $string,
            "String should have been 'string', is acturally: ".PHP_EOL.$stringObejct->text
        );
    }

    public function testGetMethod() {
        $request = new Request();
        $method = $request->getMethod();
        $this->assertTrue(
            $method === Request::METHOD_GET,
            'Default method should be GET, is actually: '.PHP_EOL.$method
        );

        $_SERVER['REQUEST_METHOD'] = Request::METHOD_DELETE; // Tested later
        $request = new Request(Request::METHOD_POST);
        $method = $request->getMethod();
        $this->assertTrue(
            $method === Request::METHOD_POST,
            'Method should be set to POST by constructor, is actually: '.PHP_EOL.$method
        );

        $request = new Request();
        $method = $request->getMethod();
        $this->assertTrue(
            $method === Request::METHOD_DELETE,
            'Method should be set to DELETE by $_SERVER, is actually: '.PHP_EOL.$method
        );
    }

    public function testGetParameter() {
        $request = new Request(
            null,
            '',
            ['true' => true, 'false' => false],
            ['HTTP_HEADER_STRING' => 'a string'],
            '{"bodyString": "a string", "object": {"integer": 3}}'
        );

        $result = $request->getParameter('this-parameter-not-set');
        $this->assertTrue(
            $result === null,
            'The default value for an unknown value, is actually: '.PHP_EOL.print_r($result, true)
        );

        $result = $request->getParameter('true');
        $this->assertTrue(
            $result === true,
            'Test parameter "true" should be true, is actually: '.PHP_EOL.print_r($result, true)
        );

        $result = $request->getParameter('false');
        $this->assertTrue(
            $result === false,
            'Test parameter "false" should be false, is actually: '.PHP_EOL.print_r($result, true)
        );

        $result = $request->getParameter('Header-String');
        $this->assertTrue(
            $result === 'a string',
            'Header-String should be "a string", is actually: '.PHP_EOL.print_r($result, true)
        );

        $result = $request->getParameter('bodyString');
        $this->assertTrue(
            $result === 'a string',
            'bodyString should be "a string", is actually: '.PHP_EOL.print_r($result, true)
        );

        $result = $request->getParameter('this-parameter-not-set');
        $this->assertTrue(
            $result === null,
            'The default value for an unknown value, is actually: '.PHP_EOL.print_r($result, true)
        );

    }

    public function testJsonSerializable() {
        $request = new Request(
            Request::METHOD_POST,
            '/test/path.xml',
            ['firstParameter' => '1'],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            '{"secondParameter":2}'
        );

        $jsonObject = json_decode(json_encode($request));

        $this->assertTrue(
            $jsonObject->method === Request::METHOD_POST,
            'Request method should be POST, is actually: '.PHP_EOL.$jsonObject->method
        );

        $this->assertTrue(
            $jsonObject->requestUri === '/test/path.xml',
            'Request URI should be /test/path.xml, is actually: '.PHP_EOL.$jsonObject->requestUri
        );

        $this->assertTrue(
            $jsonObject->method === Request::METHOD_POST,
            'Request method should be POST, is actually: '.PHP_EOL.$jsonObject->method
        );

        $this->assertTrue(
            $jsonObject->parameters->firstParameter === '1',
            'First parameter should be 1, is actually: '.PHP_EOL.print_r($jsonObject->parameters->firstParameter, true)
        );

        $this->assertTrue(
            $jsonObject->parameters->secondParameter === 2,
            'Second parameter should be 2, is actually: '.PHP_EOL.print_r($jsonObject->parameters->secondParameter, true)
        );

        // Lets check the server variable is read too
        $_SERVER['REQUEST_URI'] = '/test/path.xml?parameter=value';

        $request = new Request();
        $jsonObject = json_decode(json_encode($request));
        $this->assertTrue(
            $jsonObject->requestUri === $_SERVER['REQUEST_URI'],
            "Request URI should be {$_SERVER['REQUEST_URI']}, is actually: ".PHP_EOL.$jsonObject->requestUri
        );

    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Add parameter: parameter name must be scalar
     * @expectedExceptionCode    0
     */
    public function testAddParameterException() {
        $request = new Request();
        $request->addParameter([],[]);
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Add parameters parameter newParameters can not be scalar
     * @expectedExceptionCode    0
     */
    public function testAddParametersException() {
        $request = new Request();
        $request->addParameters(true);
    }

    public function testAddParameterFail() {
        $request = new Request();
        $this->assertTrue(
            $request->addParameter('name', 'value', false),
            "Add parameter should have returned true, it didn't"
        );

        $parameter = $request->getParameter('name');
        $this->assertTrue(
            $parameter === 'value',
            "Parameter should have been 'value', is actually: ".PHP_EOL.print_r($parameter, true)
        );

        $this->assertFalse(
            $request->addParameter('name', 'new value', false),
            "Add parameter should have returned false, it didn't"
        );
    }

    public function testReadBodyDodgily() {

        require_once 'TestData/http_get_request_body.php';

        $request = new Request();
        $hackedJson = $request->getParameter('hackedJson');
        $this->assertTrue(
            $hackedJson === true,
            'Failed to utilise hacked http_get_request_body... I\'m not sure how I feel about that'
        );
    }

    public function testGetFormatFromUri() {
        $request = new Request();

        $uri = '/test/file.php';
        $format = $request->getFormatFromUri($uri);
        $this->assertTrue(
            $format === 'php',
            'Format should be php, is actually: '.PHP_EOL.$format
        );

        $uri = '/test/file.json';
        $format = $request->getFormatFromUri($uri);
        $this->assertTrue(
            $format === 'json',
            'Format should be json, is actually: '.PHP_EOL.$format
        );

        $uri = '/test/file.json?parameters=true';
        $format = $request->getFormatFromUri($uri);
        $this->assertTrue(
            $format === 'json',
            'Format should be json, is actually: '.PHP_EOL.$format
        );
    }

}
 