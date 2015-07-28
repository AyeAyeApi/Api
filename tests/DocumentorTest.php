<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;


use AyeAye\Api\Documentor;
use AyeAye\Api\Tests\TestData\DocumentedController;

class DocumentorTest extends TestCase
{

    public function testGetParameters()
    {
        $controller = new DocumentedController();
        $documentor = new Documentor();
        $getParameters = $this->getObjectMethod($documentor, 'getParameters');

        $reflectionMethod = new \ReflectionMethod($controller, 'getDocumentedEndpoint');

        $expected = [
            'incomplete' => [
                'type' => '',
                'description' => '',
            ],
            'integer' => [
                'type' => 'int',
                'description' => 'Test integer',
            ],
            'string' => [
                'type' => 'string',
                'description' => "Test string\nSecond line",
            ],
        ];

        $this->assertSame(
            $expected,
            $getParameters($reflectionMethod)
        );
    }

}