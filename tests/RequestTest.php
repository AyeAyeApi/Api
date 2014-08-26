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
        $headers = $request->parseHeader();
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

}
 