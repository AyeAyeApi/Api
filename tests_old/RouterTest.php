<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\TestsOld;

use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Router;
use AyeAye\Api\Status;
use AyeAye\Api\TestsOld\TestData\TestController;

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

        $this->assertSame(
            $expectedEndpoint,
            $actualEndpoint
        );

        $endpoint = 'a-longer-name';
        $method = Request::METHOD_POST;
        $expectedEndpoint = 'postALongerNameEndpoint';
        $actualEndpoint = $parseEndpointName->invoke($router, $endpoint, $method);

        $this->assertSame(
            $expectedEndpoint,
            $actualEndpoint
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

        $this->assertCount(
            2,
            $controllers
        );

        $this->assertContains(
            'child',
            $controllers
        );

        $this->assertContains(
            'me',
            $controllers
        );

        $this->assertNotContains(
            'hidden-child',
            $controllers
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

        // Controllers

        $this->assertContains(
            'me',
            $result->controllers
        );

        $this->assertContains(
            'child',
            $result->controllers
        );

        $this->assertNotContains(
            'hiddenChild',
            $result->controllers
        );

        $this->assertCount(
            2,
            $result->controllers
        );

        // Endpoints

        $this->assertCount(
            3,
            $result->endpoints['get']
        );

        $this->assertArrayHasKey(
            'information',
            $result->endpoints['get']
        );

        $this->assertSame(
            'Gets some information',
            $result->endpoints['get']['information']['description']
        );

        $this->assertCount(
            0,
            $result->endpoints['get']['information']['parameters']
        );

        $this->assertArrayHasKey(
            'more-information',
            $result->endpoints['get']
        );

        $this->assertSame(
            'Get some conditional information',
            $result->endpoints['get']['more-information']['description']
        );

        $this->assertCount(
            1,
            $result->endpoints['get']['more-information']['parameters']
        );

        $this->assertSame(
            'string',
            $result->endpoints['get']['more-information']['parameters']['condition']->type
        );

        $this->assertSame(
            'The condition for the information',
            $result->endpoints['get']['more-information']['parameters']['condition']->description
        );

        $this->assertCount(
            1,
            $result->endpoints['put']
        );

        $this->assertArrayHasKey(
            'information',
            $result->endpoints['put']
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

        $this->assertObjectHasAttribute(
            'endpoints',
            $result
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

        $this->assertObjectHasAttribute(
            'endpoints',
            $result
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

        $this->assertSame(
            'information',
            $result
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

        $this->assertSame(
            'string',
            $result->param1
        );
        $this->assertSame(
            9001,
            $result->param2
        );
        $this->assertSame(
            true,
            $result->param3
        );
        $this->assertSame(
            false,
            $result->param4
        );
    }

    public function testMethodDocumentationParsing()
    {
        $router = new Router();
        $controller = new TestController;

        $result = $router->getMethodDocumentation($controller, "getRandomIndentationEndpoint");

        
        $this->assertArrayHasKey(
            'description',
            $result
        );

        $this->assertArrayHasKey(
            'parameters',
            $result
        );

        $this->assertSame(
            'This is the endpoint where PHPDoc is indented randomly',
            $result["description"]
        );

        $parameters = $result["parameters"];

        $this->assertArrayHasKey(
            'first',
            $parameters
        );

         $this->assertArrayHasKey(
             'second',
             $parameters
         );

         $first = $parameters["first"];
         $second = $parameters["second"];

         $this->assertObjectHasAttribute(
             'type',
             $first
         );

         $this->assertObjectHasAttribute(
             'description',
             $first
         );

         $this->assertObjectHasAttribute(
             'type',
             $second
         );

         $this->assertObjectHasAttribute(
             'description',
             $second
         );

         $this->assertSame(
             'string',
             $first->type
         );

         $this->assertSame(
             'Some string',
             $first->description
         );

         $this->assertSame(
             'float',
             $second->type
         );

         $this->assertSame(
             'Parameter with different indentation',
             $second->description
         );
    }
}
