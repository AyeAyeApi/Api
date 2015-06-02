<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Router;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\DocumentedController;

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

    public function testParseControllerName()
    {
        $router = new Router();
        $parseControllerName = $this->getObjectMethod($router, 'parseControllerName');

    }

    public function testStatus()
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