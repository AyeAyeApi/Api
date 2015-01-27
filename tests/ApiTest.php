<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Api;
use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\TestController;
use AyeAye\Formatter\FormatFactory;
use AyeAye\Formatter\Formats\Php;
use AyeAye\Formatter\Formats\Xml;

/**
 * Test for the Api Class
 * @package AyeAye\Api\Tests
 */
class ApiTest extends TestCase
{

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
            'me', $output->data->controllers,
            "Controllers should have contained 'me'"
        );

        $this->assertContains(
            'child', $output->data->controllers,
            "Controllers should have contained 'child'"
        );

        $this->assertNotContains(
            'hiddenChild', $output->data->controllers,
            "Controllers should not have contained 'hiddenChild'"
        );

        $this->assertCount(
            2, $output->data->controllers,
            "Controllers should have has 2 elements"
        );

        // Endpoints


        $this->assertObjectHasAttribute(
			'information', $output->data->endpoints->get,
            "Get endpoints should have included 'information' it didn't"
        );

        $this->assertSame(
			'Gets some information', $output->data->endpoints->get->information->description,
            "Get Information description was wrong"
        );

        $this->assertCount(
            0, $output->data->endpoints->get->information->parameters,
            "Get Information description should not contain any parameters"
        );

        $this->assertObjectHasAttribute(
			'more-information', $output->data->endpoints->get,
            "Get endpoints should have included more-information it didn't"
        );

        $this->assertSame(
			'Get some conditional information', $output->data->endpoints->get->{'more-information'}->description,
            "Get More Information description was wrong"
        );

        $this->assertSame(
			'string', $output->data->endpoints->get->{'more-information'}->parameters->condition->type,
            "Get More Information should take a string called condition"
        );

        $this->assertSame(
			'The condition for the information', $output->data->endpoints->get->{'more-information'}->parameters->condition->description,
            "Get More Information parameter should be described as 'The condition for the information'"
        );

		$this->assertTrue(
			count($output->data->endpoints->put) === 1,
			"There should have been 1 get endpoints, there were: " . PHP_EOL . count($output->data->endpoints->put)
		);

        $this->assertObjectHasAttribute(
            'information', $output->data->endpoints->put,
            "Put endpoints should have included 'information' it didn't"
        );

    }

	/**
	 * Test the errors are reported to the client correctly
	 * @see Api
	 * @see TestController
	 * @runInSeparateProcess
	 */
	public function testInvalidEndpoint() {
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
			$output->data, "Could not find controller or endpoint matching 'not-a-real-endpoint'",
			'Exception should have been caught and returned an appropriate error to the user'
		);

        $this->assertSame(
            $response->getStatus()->getHttpHeader(), 'HTTP/1.1 404 Not Found',
            'Incorrect header response, got '.$response->getStatus()->getHttpHeader()
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

        $this->assertSame(
            $testRequest->getParameter('key'),
            null,
            "Request should not contain a parameter for 'key'"
        );

        $api->setRequest($request);

        $testRequest = $api->getRequest();

        $this->assertSame(
            $testRequest->getParameter('key'),
            'value',
            "Request should contain a parameter for 'key'"
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
        $response->setData('test-data');

        $testResponse = $api->getResponse();

        $this->assertSame(
            $testResponse->getData(),
            null,
            "Response should not contain any data"
        );

        $api->setResponse($response);

        $testResponse = $api->getResponse();

        $this->assertSame(
            $testResponse->getData(),
            'test-data',
            "Request should contain test data"
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

        $this->assertTrue(
            $testFormatFactory->getFormatFor('xml') instanceof Xml,
            "Response should not contain any data"
        );

        $api->setFormatFactory($formatFactory);

        $testFormatFactory = $api->getFormatFactory();

        $this->assertTrue(
            $testFormatFactory->getFormatFor('php') instanceof Php,
            "Response should not contain any data"
        );

    }


}
 
