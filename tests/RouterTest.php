<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Request;
use AyeAye\Api\Router;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\DocumentedController;
use AyeAye\Api\Tests\TestData\IndexedController;

/**
 * Class RouterTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass AyeAye\Api\Router
 */
class RouterTest extends TestCase
{

    /**
     * @test
     * @covers ::processRequest
     * @uses AyeAye\Api\Documentor
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::getEndpoints
     * @uses AyeAye\Api\Router::getControllers
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     * @uses AyeAye\Api\Router::documentController
     * @uses AyeAye\Api\Router::getParametersFromRequest
     */
    public function testProcessRequestSelfDocumented()
    {
        $controller = new DocumentedController();
        $request = new Request();

        $router = new Router();
        $response = $router->processRequest($request, $controller);
        $this->assertObjectHasAttribute(
            'controllers',
            $response
        );
        $this->assertObjectHasAttribute(
            'endpoints',
            $response
        );

    }

    /**
     * @test
     * @covers ::processRequest
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::parseControllerName
     * @uses AyeAye\Api\Router::getParametersFromRequest
     * @uses AyeAye\Api\Router::setStatus
     */
    public function testProcessRequestEndpointOnly()
    {
        $controller = new DocumentedController();
        $request = new Request('GET', 'documented');

        $router = new Router();
        $this->assertSame(
            'information',
            $router->processRequest($request, $controller, null)
        );

    }

    /**
     * @test
     * @covers ::processRequest
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::parseControllerName
     * @uses AyeAye\Api\Router::getParametersFromRequest
     * @uses AyeAye\Api\Router::setStatus
     */
    public function testProcessRequestIndexedController()
    {
        $controller = new IndexedController();

        $request = new Request('GET', '');
        $router = new Router();
        $this->assertSame(
            'Got Index',
            $router->processRequest($request, $controller, null)
        );

        $request = new Request('PUT', '');
        $router = new Router();
        $this->assertSame(
            'Put Index',
            $router->processRequest($request, $controller, null)
        );

    }

    /**
     * @test
     * @covers ::processRequest
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::parseControllerName
     * @uses AyeAye\Api\Router::getParametersFromRequest
     * @uses AyeAye\Api\Router::setStatus
     */
    public function testProcessRequestControllerToEndpoint()
    {
        $controller = new DocumentedController();
        $request = new Request('GET', 'self-reference/documented');

        $router = new Router();
        $this->assertSame(
            'information',
            $router->processRequest($request, $controller, null)
        );

    }

    /**
     * @test
     * @expectedException        \AyeAye\Api\Exception
     * @expectedExceptionCode    404
     * @expectedExceptionMessage Could not find controller or endpoint matching 'nonsense'
     * @covers ::processRequest
     * @uses AyeAye\Api\Exception
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::parseControllerName
     */
    public function testProcessRequestNotFound()
    {
        $controller = new DocumentedController();
        $request = new Request('GET', 'nonsense');
        $router = new Router();
        $router->processRequest($request, $controller, null);

    }


    /**
     * @test
     * @covers ::documentController
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Documentor
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::getEndpoints
     * @uses AyeAye\Api\Router::getControllers
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     */
    public function testDocumentController()
    {
        $controller = new DocumentedController();
        $router = new Router();

        $documentController = $this->getObjectMethod($router, 'documentController');
        $documentation = $documentController($controller);
        $this->assertObjectHasAttribute(
            'controllers',
            $documentation
        );
        $this->assertObjectHasAttribute(
            'endpoints',
            $documentation
        );

    }

    /**
     * @test
     * @covers ::camelcaseToHyphenated
     */
    public function testCamelcaseToHyphenated()
    {
        $router = new Router();
        $camelcaseToHyphenated = $this->getObjectMethod($router, 'camelcaseToHyphenated');

        $this->assertSame(
            'camelcase-to-hyphenated',
            $camelcaseToHyphenated('camelcaseToHyphenated')
        );
    }

    /**
     * @test
     * @covers ::getEndpoints
     * @uses AyeAye\Api\Documentor
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     */
    public function testGetEndpoints()
    {
        $router = new Router();
        $getEndpoints = $this->getObjectMethod($router, 'getEndpoints');

        $controller = new DocumentedController();
        $endpoints = $getEndpoints($controller);
        $this->assertArrayHasKey(
            'get',
            $endpoints
        );
        $this->assertArrayNotHasKey(
            'put',
            $endpoints
        );

        $controller = new IndexedController();
        $endpoints = $getEndpoints($controller);
        $this->assertArrayHasKey(
            'get',
            $endpoints
        );
        $this->assertArrayHasKey(
            'put',
            $endpoints
        );
    }

    /**
     * @test
     * @covers ::getControllers
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     */
    public function testGetControllers()
    {
        $router = new Router();
        $getControllers = $this->getObjectMethod($router, 'getControllers');

        $controller = new DocumentedController();
        $controllers = $getControllers($controller);
        $this->assertContains(
            'self-reference',
            $controllers
        );
    }

    /**
     * @test
     * @covers ::parseEndpointName
     */
    public function testParseEndpointName()
    {
        $router = new Router();
        $parseEndpointName = $this->getObjectMethod($router, 'parseEndpointName');

        $this->assertSame(
            'getTestEndpoint',
            $parseEndpointName('test')
        );

        $this->assertSame(
            'putCamelCaseEndpoint',
            $parseEndpointName('camel-case', 'put')
        );

        $this->assertSame(
            'postCamelCaseEndpoint',
            $parseEndpointName('camel+case', 'POST')
        );

        $this->assertSame(
            'optionsCamelCaseEndpoint',
            $parseEndpointName('camel%20case', 'oPtIoNs')
        );
    }

    /**
     * @test
     * @covers ::parseControllerName
     */
    public function testParseControllerName()
    {
        $router = new Router();
        $parseControllerName = $this->getObjectMethod($router, 'parseControllerName');

        // ToDo: Should this be an error?
        $this->assertSame(
            'Controller',
            $parseControllerName('')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel-case')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel%20case')
        );

        $this->assertSame(
            'camelCaseController',
            $parseControllerName('camel+case')
        );
    }

    /**
     * @test
     * @covers ::getParametersFromRequest
     * @uses AyeAye\Api\Request
     */
    public function testGetParametersFromRequest()
    {
        $router = new Router();
        $request = new Request(null, null, [
            'integer' => 20,
            'string' => false,
            'not-used' => 'anything',
        ]);
        $controller = new DocumentedController();
        $method = 'getDocumentedEndpoint';

        $getParametersFromRequest = $this->getObjectMethod($router, 'getParametersFromRequest');

        $this->assertSame(
            [
                'incomplete' => null,
                'integer' => 20,
                'string' => false,
            ],
            $getParametersFromRequest($request, $controller, $method)
        );
    }

    /**
     * @test
     * @covers ::getStatus
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Router::setStatus
     */
    public function testGetStatus()
    {
        $router = new Router();

        $this->assertSame(
            200,
            $router->getStatus()->getCode()
        );
        $this->assertSame(
            'OK',
            $router->getStatus()->getMessage()
        );

        $status = new Status(500);
        $setStatus = $this->getObjectMethod($router, 'setStatus');
        $setStatus($status);

        $this->assertSame(
            500,
            $router->getStatus()->getCode()
        );
        $this->assertSame(
            'Internal Server Error',
            $router->getStatus()->getMessage()
        );
    }

    /**
     * @test
     * @covers ::setStatus
     * @uses AyeAye\Api\Status
     * @uses AyeAye\Api\Router::getStatus
     */
    public function testSetStatus()
    {
        $status = new Status(418);
        $router = new Router();

        $setStatus = $this->getObjectMethod($router, 'setStatus');
        $setStatus($status);
        $this->assertSame(
            $status,
            $router->getStatus()
        );
    }

}