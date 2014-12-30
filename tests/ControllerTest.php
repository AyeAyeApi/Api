<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests;


use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\TestController;

/**
 * Test for the Controller Class
 * @package AyeAye\Api\Tests
 */
class ControllerTest extends TestCase
{

    /**
     * Test the parse action method
     * Checks simple and long names
     * @see Controller
     */
    public function testParseActionName()
    {
        $controller = new Controller();

        $action = 'index';
        $method = Request::METHOD_GET;
        $expectedEndpoint = 'getIndexAction';
        $actualEndpoint = $controller->parseActionName($action, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: " . PHP_EOL . $actualEndpoint
        );

        $action = 'a-longer-name';
        $method = Request::METHOD_POST;
        $expectedEndpoint = 'postALongerNameAction';
        $actualEndpoint = $controller->parseActionName($action, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: " . PHP_EOL . $actualEndpoint
        );
    }

    /**
     * Tests the controller returns the correct status
     * Tests default status and 418 status, looks at code and message
     * @see Controller
     * @see Status
     */
    public function testStatus()
    {

        // Default behaviour is OK

        $controller = new Controller();
        $status = $controller->getStatus();

        $this->assertTrue(
            $status instanceof Status,
            "status was expected to be type Status, is actually: " . PHP_EOL . get_class($status)
        );

        $this->assertTrue(
            $status->getCode() === 200,
            "status was expected to be code 200, is actually: " . PHP_EOL . $status->getCode()
        );

        $this->assertTrue(
            $status->getMessage() === 'OK',
            "status was expected to be 'OK', is actually: " . PHP_EOL . $status->getMessage()
        );

        $controller = new Controller();
        $controller->setStatusCode(418);
        $status = $controller->getStatus();

        $this->assertTrue(
            $status instanceof Status,
            "status was expected to be type Status, is actually: " . PHP_EOL . get_class($status)
        );

        $this->assertTrue(
            $status->getCode() === 418,
            "status was expected to be code 418, is actually: " . PHP_EOL . $status->getCode()
        );

        $this->assertTrue(
            $status->getMessage() === "I'm a teapot",
            "status was expected to be 'I'm a teapot', is actually: " . PHP_EOL . $status->getMessage()
        );

    }

    /**
     * Tests controller returns all relevant children, including ignored
     * @see TestController
     */
    public function testGetControllers()
    {
        $controller = new TestController();
        $children = $controller->getControllers();

        $this->assertTrue(
            count($children) === 2
        );

        $this->assertTrue(
            in_array('child', $children),
            'Children should have included child'
        );

        $this->assertTrue(
            in_array('me', $children),
            'Children should have included me'
        );

        $this->assertFalse(
            in_array('hidden-child', $children),
            'Children should have included me'
        );
    }

    /**
     * Tests the correct data is returned for the index action
     * @see TestController
     */
    public function testIndexAction()
    {
        $controller = new TestController();
        $result = $controller->getIndexAction();

        // Children

        $this->assertTrue(
            in_array('me', $result->controllers),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            in_array('child', $result->controllers),
            "Children should have contained 'me'"
        );

        $this->assertFalse(
            in_array('hiddenChild', $result->controllers),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            count($result->controllers) == 2,
            "Children should have has 2 elements, it had: " . PHP_EOL . count($result->controllers)
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
            $result->endpoints['get']['more-information']['parameters']['condition']->description === 'The condition for the information',
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
        $controller = new TestController();
        $result = $controller->processRequest($request, []);

        $this->assertTrue(
            property_exists($result, 'endpoints'),
            "Default index action not hit"
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
        $controller = new TestController();
        $result = $controller->processRequest($request);

        $this->assertTrue(
            property_exists($result, 'endpoints'),
            "Alternative index action not hit"
        );
    }

    /**
     * Tests an incorrect route returns a 404
     * @see Request
     * @see TestController
     * @expectedException        \Exception
     * @expectedExceptionMessage Could not find controller or action matching
     * @expectedExceptionCode    404
     */
    public function testActionNotFound()
    {
        $request = new Request(
            Request::METHOD_GET,
            'not-a-real-action.json'
        );
        $controller = new TestController();
        $controller->processRequest(
            $request,
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
        $controller = new TestController();
        $result = $controller->processRequest($request);

        $this->assertEquals(
            $result,
            'information',
            "Correct action not hit"
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
        $controller = new TestController();
        $result = $controller->processRequest($request);

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
 