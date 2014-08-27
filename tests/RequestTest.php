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
    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Non-string passed to stringToObject
     * @expectedExceptionCode    0
     */
    public function testStringToObjectNonString() {
        $request = new Request();
        $request->stringToObject(true);
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

}
 