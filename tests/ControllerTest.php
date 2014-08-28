<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Controller;
use Gisleburt\Api\Request;
use Gisleburt\Api\Status;
use Gisleburt\Api\Tests\TestData\TestController;

class ControllerTest extends TestCase {

    public function testParseActionName() {
        $controller = new Controller();

        $action = 'index';
        $method = Request::METHOD_GET;
        $expectedEndpoint = 'getIndexAction';
        $actualEndpoint = $controller->parseActionName($action, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: ".PHP_EOL.$actualEndpoint
        );

        $action = 'a-longer-name';
        $method = Request::METHOD_POST;
        $expectedEndpoint = 'postALongerNameAction';
        $actualEndpoint = $controller->parseActionName($action, $method);

        $this->assertTrue(
            $actualEndpoint === $expectedEndpoint,
            "Expected $expectedEndpoint, is actually: ".PHP_EOL.$actualEndpoint
        );
    }

    public function testStatus() {

        // Default behaviour is OK

        $controller = new Controller();
        $status = $controller->getStatus();

        $this->assertTrue(
            $status instanceof Status,
            "status was expected to be type Status, is actually: ".PHP_EOL.get_class($status)
        );

        $this->assertTrue(
            $status->getCode() === 200,
            "status was expected to be code 200, is actually: ".PHP_EOL.$status->getCode()
        );

        $this->assertTrue(
            $status->getMessage() === 'OK',
            "status was expected to be 'OK', is actually: ".PHP_EOL.$status->getMessage()
        );

        $controller = new Controller();
        $controller->setStatusCode(418);
        $status = $controller->getStatus();

        $this->assertTrue(
            $status instanceof Status,
            "status was expected to be type Status, is actually: ".PHP_EOL.get_class($status)
        );

        $this->assertTrue(
            $status->getCode() === 418,
            "status was expected to be code 418, is actually: ".PHP_EOL.$status->getCode()
        );

        $this->assertTrue(
            $status->getMessage() === "I'm a teapot",
            "status was expected to be 'I'm a teapot', is actually: ".PHP_EOL.$status->getMessage()
        );

    }

    public function testGetChildren() {
        $controller = new TestController();
        $children = $controller->getChildren();

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
            in_array('hiddenChild', $children),
            'Children should have included me'
        );
    }

    public function testIndexAction() {
        $controller = new TestController();
        $result = $controller->getIndexAction();

        // Children

        $this->assertTrue(
            in_array('me', $result->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            in_array('child', $result->children),
            "Children should have contained 'me'"
        );

        $this->assertFalse(
            in_array('hiddenChild', $result->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            count($result->children) == 2,
            "Children should have has 2 elements, it had: ".PHP_EOL.count($result->children)
        );

        // Endpoints

        $this->assertTrue(
            count($result->endpoints['get']) == 2,
            "There should have been 2 get endpoints, there were: ".PHP_EOL.count($result->endpoints['get'])
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
            "There should have been 1 get endpoints, there were: ".PHP_EOL.count($result->endpoints['put'])
        );

        $this->assertTrue(
            array_key_exists('information', $result->endpoints['put']),
            "Put endpoints should have included 'information' it didn't"
        );

    }

}
 