<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests;

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