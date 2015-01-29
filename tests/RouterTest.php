<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Router;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\TestController;

/**
 * Test for the Controller Class
 * @package AyeAye\Api\Tests
 */
class RouterTest extends TestCase
{

    public function testStatusCode()
    {
        $router = new Router();
        $status = $router->getStatus();

        $this->assertSame(
            200,
            $status->getCode()
        );

        $this->assertSame(
            'OK',
            $status->getMessage()
        );

        $setStatus = $this->getClassMethod($router, 'setStatus');
        $setStatus->invoke($router, new Status(500));
        $status = $router->getStatus();

        $this->assertSame(
            500,
            $status->getCode()
        );

        $this->assertSame(
            'Internal Server Error',
            $status->getMessage()
        );

        $router = new TestController();
        $setStatusCode = $this->getClassMethod($router, 'setStatusCode');
        $setStatusCode->invoke($router, 418);
        $status = $router->getStatus();

        $this->assertSame(
            418,
            $status->getCode()
        );

        $this->assertSame(
            'I\'m a teapot',
            $status->getMessage()
        );
    }

    /**
     * Test the parse endpoint method
     * Checks simple and long names
     * @see Controller
     */
    public function testParseEndpointName()
    {
        $router = new Router();

        $parseEndpointName = $this->getClassMethod($router, 'parseEndpointName');

        $endpoint = 'index';
        $method = Request::METHOD_GET;
        $expectedEndpoint = 'getIndexEndpoint';
        $actualEndpoint = $parseEndpointName->invoke($router, $endpoint, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: " . PHP_EOL . $actualEndpoint
        );

        $endpoint = 'a-longer-name';
        $method = Request::METHOD_POST;
        $expectedEndpoint = 'postALongerNameEndpoint';
        $actualEndpoint = $parseEndpointName->invoke($router, $endpoint, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: " . PHP_EOL . $actualEndpoint
        );
    }

    /**
     * Tests controller returns all relevant controllers, including ignored
     * @see TestController
     */
    public function testGetControllers()
    {
        $controller = new TestController();
        $router = new Router();
        $controllers = $router->getControllers($controller);

        $this->assertTrue(
            count($controllers) === 2
        );

        $this->assertTrue(
            in_array('child', $controllers),
            'Controllers should have included child'
        );

        $this->assertTrue(
            in_array('me', $controllers),
            'Controllers should have included me'
        );

        $this->assertFalse(
            in_array('hidden-child', $controllers),
            'Controllers should have included me'
        );
    }

    /**
     * Tests the correct data is returned for the index endpoint
     * @see TestController
     */
    public function testDocumentController()
    {
        $router = new Router();
        $controller = new TestController();
        $result = $router->documentController($controller);
//print_r($result); die;
        // Controllers

        $this->assertTrue(
            in_array('me', $result->controllers),
            "Controllers should have contained 'me'"
        );

        $this->assertTrue(
            in_array('child', $result->controllers),
            "Controllers should have contained 'me'"
        );

        $this->assertFalse(
            in_array('hiddenChild', $result->controllers),
            "Controllers should have contained 'me'"
        );

        $this->assertTrue(
            count($result->controllers) == 2,
            "Controllers should have has 2 elements, it had: " . PHP_EOL . count($result->controllers)
        );

        // Endpoints

        $this->assertTrue(
            count($result->endpoints['get']) == 2,
            "There should have been 2 get endpoints, there were: " . PHP_EOL . count($result->endpoints['get'])
        );

        $this->assertTrue(
            array_key_exists('information', $result->endpoints['get']),
            "Get endpoints should have included 'information' it didn't"
        );

        $this->assertTrue(
            $result->endpoints['get']['information']['description'] === 'Gets some information',
            "Get Information description was wrong"
        );

        $this->assertTrue(
            count($result->endpoints['get']['information']['parameters']) === 0,
            "Get Information description should not contain any parameters"
        );

        $this->assertTrue(
            array_key_exists('more-information', $result->endpoints['get']),
            "Get endpoints should have included 'more-information' it didn't"
        );

        $this->assertTrue(
            $result->endpoints['get']['more-information']['description'] === 'Get some conditional information',
            "Get More Information description was wrong"
        );

        $this->assertTrue(
            count($result->endpoints['get']['more-information']['parameters']) === 1,
            "Get More Information description should not contain any parameters"
        );

        $this->assertTrue(
            $result->endpoints['get']['more-information']['parameters']['condition']->type === 'string',
            "Get More Information should take a string called condition"
        );

        $this->assertTrue(
            $result->endpoints['get']['more-information']['parameters']['condition']->description
            === 'The condition for the information',
            "Get More Information parameter should be described as 'The condition for the information'"
        );

        $this->assertTrue(
            count($result->endpoints['put']) === 1,
            "There should have been 1 get endpoints, there were: " . PHP_EOL . count($result->endpoints['put'])
        );

        $this->assertTrue(
            array_key_exists('information', $result->endpoints['put']),
            "Put endpoints should have included 'information' it didn't"
        );

    }

    /**
     * Tests routing with empty request chain
     * @see Request
     * @see TestController
     */
    public function testDefaultIndexRoute()
    {
        $request = new Request();
        $router = new Router();
        $controller = new TestController();
        $result = $router->processRequest($request, $controller, []);

        $this->assertTrue(
            property_exists($result, 'endpoints'),
            "Default index endpoint not hit"
        );
    }

    /**
     * Tests routing with a post request that goes no where
     * @see Request
     * @see TestController
     */
    public function testPostIndexRoute()
    {
        $request = new Request(Request::METHOD_POST);
        $router = new Router();
        $controller = new TestController();
        $result = $router->processRequest($request, $controller, []);

        $this->assertSame(
            'Why are you posting to the index?',
            $result
        );
    }

    /**
     * Tests process request method works without giving a request chain, taking it from Request object
     * @see Request
     * @see TestController
     */
    public function testAlternativeIndexRoute()
    {
        $request = new Request(Request::METHOD_PUT);
        $router = new Router();
        $controller = new TestController();
        $result = $router->processRequest($request, $controller);

        $this->assertTrue(
            property_exists($result, 'endpoints'),
            "Alternative index endpoint not hit"
        );
    }

    /**
     * Tests an incorrect route returns a 404
     * @see Request
     * @see TestController
     * @expectedException        \Exception
     * @expectedExceptionMessage Could not find controller or endpoint matching
     * @expectedExceptionCode    404
     */
    public function testEndpointNotFound()
    {
        $request = new Request(
            Request::METHOD_GET,
            'not-a-real-endpoint.json'
        );
        $router = new Router();
        $controller = new TestController();
        $router->processRequest(
            $request,
            $controller,
            $request->getRequestChain()
        );
    }

    /**
     * Tests that a given request chain is routed correctly using only the Request object
     * @see Request
     * @see TestController
     */
    public function testKnownRoute()
    {
        $request = new Request(
            Request::METHOD_GET,
            'information'
        );
        $router = new Router();
        $controller = new TestController();
        $result = $router->processRequest($request, $controller);

        $this->assertEquals(
            $result,
            'information',
            "Correct endpoint not hit"
        );
    }

    public function testParametersFromRequest()
    {
        $request = new Request(
            Request::METHOD_POST,
            'child/complex-data',
            [
                'param1' => 'string',
                'param2' => 9001,
                'param3' => true,
                'param4' => false,
            ]
        );
        $router = new Router();
        $controller = new TestController();
        $result = $router->processRequest($request, $controller);

        $this->assertEquals(
            $result->param1,
            'string',
            "Data not parsed correctly"
        );
        $this->assertEquals(
            $result->param2,
            9001,
            "Data not parsed correctly"
        );
        $this->assertEquals(
            $result->param3,
            true,
            "Data not parsed correctly"
        );
        $this->assertEquals(
            $result->param4,
            false,
            "Data not parsed correctly"
        );
    }
}
