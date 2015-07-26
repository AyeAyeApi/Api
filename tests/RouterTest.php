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
     * @uses AyeAye\Api\Request
     * @uses AyeAye\Api\Controller
     * @uses AyeAye\Api\Router::parseEndpointName
     * @uses AyeAye\Api\Router::getEndpoints
     * @uses AyeAye\Api\Router::getControllers
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     * @uses AyeAye\Api\Router::documentController
     * @uses AyeAye\Api\Router::getParametersFromRequest
     * @uses AyeAye\Api\Router::getMethodDocumentation
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
     * @covers ::camelcaseToHyphenated
     */
    public function testCamelcaseToHyphenated() {
        $router = new Router();
        $camelcaseToHyphenated = $this->getObjectMethod($router, 'camelcaseToHyphenated');

        $this->assertSame(
            'camelcase-to-hyphenated',
            $camelcaseToHyphenated('camelcaseToHyphenated')
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
        $router->setStatus($status);

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

        $router->setStatus($status);
        $this->assertSame(
            $status,
            $router->getStatus()
        );
    }

    /**
     * @test
     * @covers ::getMethodDocumentation
     * @uses AyeAye\Api\Router::camelcaseToHyphenated
     */
    public function testGetMethodDocumentation()
    {
        $router = new Router();
        $controller = new DocumentedController();
        // ToDo: This should be private
        $getMethodDocumentation = $this->getObjectMethod($router, 'getMethodDocumentation');
        $documentation = $getMethodDocumentation($controller, 'getDocumentedEndpoint');

        $this->assertArrayHasKey(
            'description',
            $documentation
        );

        $this->assertSame(
            'Test Summary Test Description.',
            $documentation['description']
        );
    }

}