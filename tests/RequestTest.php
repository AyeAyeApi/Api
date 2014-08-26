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

}
 