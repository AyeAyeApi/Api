<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\TestsOld;

use AyeAye\Api\Api;
use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\TestsOld\TestData\TestController;
use AyeAye\Api\TestsOld\TestData\TestRouter;
use AyeAye\Formatter\FormatFactory;
use AyeAye\Formatter\Formats\Php;
use AyeAye\Formatter\Formats\Xml;

/**
 * Test for the Api Class
 * @package AyeAye\Api\Tests
 */
class ApiTest extends TestCase
{

    public function testSetRouter()
    {
        $controller = new TestController();
        $api = new Api($controller);
        $router = $api->getRouter();
        $this->assertInstanceOf(
            '\AyeAye\Api\Router',
            $router
        );
        $this->assertNotInstanceOf(
            '\AyeAye\Api\TestsOld\TestData\TestRouter',
            $router
        );

        $testRouter = new TestRouter();
        $api->setRouter($testRouter);
        $router = $api->getRouter();
        $this->assertInstanceOf(
            '\AyeAye\Api\Router',
            $router
        );
        $this->assertInstanceOf(
            '\AyeAye\Api\TestsOld\TestData\TestRouter',
            $router
        );

        $controller = new TestController();
        $router = new TestRouter();
        $api = new Api($controller, $router);
        $router = $api->getRouter();
        $this->assertInstanceOf(
            '\AyeAye\Api\Router',
            $router
        );
        $this->assertInstanceOf(
            '\AyeAye\Api\TestsOld\TestData\TestRouter',
            $router
        );
    }

    /**
     * Test the output of the Api using TestController
     * @see Api
     * @see TestController
     * @runInSeparateProcess
     */
    public function testQuickStart()
    {
        $initialController = new TestController();
        $api = new Api($initialController);

        ob_start();

        $api->go()->respond();

        $output = json_decode(ob_get_clean());


        // Controllers

        $this->assertContains(
            'me',
            $output->data->controllers
        );

        $this->assertContains(
            'child',
            $output->data->controllers
        );

        $this->assertNotContains(
            'hidden-child',
            $output->data->controllers
        );

        $this->assertCount(
            2,
            $output->data->controllers
        );

        // Endpoints

        $this->assertObjectHasAttribute(
            'information',
            $output->data->endpoints->get
        );

        $this->assertSame(
            'Gets some information',
            $output->data->endpoints->get->information->description
        );

        $this->assertEmpty(
            $output->data->endpoints->get->information->parameters
        );

        $this->assertObjectHasAttribute(
            'more-information',
            $output->data->endpoints->get
        );

        $this->assertSame(
            'Get some conditional information',
            $output->data->endpoints->get->{'more-information'}->description
        );

        $this->assertSame(
            'string',
            $output->data->endpoints->get->{'more-information'}->parameters->condition->type
        );

        $this->assertSame(
            'The condition for the information',
            $output->data->endpoints->get->{'more-information'}->parameters->condition->description
        );

        $this->assertTrue(
            count($output->data->endpoints->put) === 1
        );

        $this->assertObjectHasAttribute(
            'information',
            $output->data->endpoints->put
        );

    }

    /**
     * Test the errors are reported to the client correctly
     * @see Api
     * @see TestController
     * @runInSeparateProcess
     */
    public function testInvalidEndpoint()
    {
        $initialController = new TestController();
        $request = new Request(
            Request::METHOD_GET,
            '/not-a-real-endpoint'
        );
        $api = new Api($initialController);
        $api->setRequest($request);

        ob_start();

        $response = $api->go();
        $response->respond();

        $output = json_decode(ob_get_clean());

        $this->assertSame(
            $output->data,
            "Could not find controller or endpoint matching 'not-a-real-endpoint'"
        );

        $this->assertSame(
            $response->getStatus()->getHttpHeader(),
            'HTTP/1.1 404 Not Found'
        );

    }

    /**
     * Tests setting and retrieving a Request object
     * @see Api
     * @see Controller
     * @see Request
     */
    public function testSetRequest()
    {
        $initialController = new Controller(); // Unimportant
        $api = new Api($initialController);

        $request = new Request(
            null,
            null,
            ['key' => 'value']
        );

        $testRequest = $api->getRequest();

        $this->assertNull(
            $testRequest->getParameter('key')
        );

        $api->setRequest($request);

        $testRequest = $api->getRequest();

        $this->assertSame(
            $testRequest->getParameter('key'),
            'value'
        );

    }

    /**
     * Tests setting and retrieving a Response object
     * @see Api
     * @see Controller
     * @see Response
     */
    public function testSetResponse()
    {
        $initialController = new Controller(); // Unimportant
        $api = new Api($initialController);

        $response = new Response();
        $response->setBodyData('test-data');

        $testResponse = $api->getResponse();

        $this->assertNull(
            $testResponse->getData()
        );

        $api->setResponse($response);

        $testResponse = $api->getResponse();

        $this->assertSame(
            $testResponse->getData(),
            'test-data'
        );

    }

    /**
     * Tests setting and retrieving a Format Factory object
     * @see Api
     * @see Controller
     * @see FormatFactory
     */
    public function testFormatFactory()
    {
        $initialController = new Controller(); // Unimportant
        $api = new Api($initialController);

        $formatFactory = new FormatFactory([
            'php' => new Php()
        ]);

        $testFormatFactory = $api->getFormatFactory();

        $this->assertInstanceOf(
            'AyeAye\Formatter\Formats\Xml',
            $testFormatFactory->getFormatterFor('xml')
        );

        $api->setFormatFactory($formatFactory);

        $testFormatFactory = $api->getFormatFactory();

        $this->assertInstanceOf(
            'AyeAye\Formatter\Formats\Php',
            $testFormatFactory->getFormatterFor('php')
        );

    }
}
