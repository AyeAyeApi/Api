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


        // Children

        $this->assertTrue(
            in_array('me', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            in_array('child', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertFalse(
            in_array('hiddenChild', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            count($output->data->children) == 2,
            "Children should have has 2 elements, it had: " . PHP_EOL . count($output->data->children)
        );

        // Endpoints


        $this->assertTrue(
            property_exists($output->data->endpoints->get, 'information'),
            "Get endpoints should have included 'information' it didn't"
        );

        $this->assertTrue(
            $output->data->endpoints->get->information->description === 'Gets some information',
            "Get Information description was wrong"
        );

        $this->assertTrue(
            count($output->data->endpoints->get->information->parameters) === 0,
            "Get Information description should not contain any parameters"
        );

        $this->assertTrue(
            property_exists($output->data->endpoints->get, 'more-information'),
            "Get endpoints should have included more-information it didn't"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->description === 'Get some conditional information',
            "Get More Information description was wrong"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->parameters->condition->type === 'string',
            "Get More Information should take a string called condition"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->parameters->condition->description === 'The condition for the information',
            "Get More Information parameter should be described as 'The condition for the information'"
        );

        $this->assertTrue(
            count($output->data->endpoints->put) === 1,
            "There should have been 1 get endpoints, there were: " . PHP_EOL . count($output->data->endpoints->put)
        );

        $this->assertTrue(
            array_key_exists('information', $output->data->endpoints->put),
            "Put endpoints should have included 'information' it didn't"
        );

    }

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
 
