<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;


use AyeAye\Api\Api;
use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Router;
use AyeAye\Formatter\FormatFactory;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class ApiTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\Api
 */
class ApiTest extends TestCase
{

    /**
     * @test
     * @covers ::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setLogger
     * @uses \AyeAye\Api\Api::setRouter
     */
    public function testConstruct()
    {
        $controller = new Controller();
        $router = new Router();
        $logger = new NullLogger();

        $api = new Api($controller);
        $this->assertSame(
            $controller,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertNull(
            $this->getObjectAttribute($api, 'router')
        );
        $this->assertNull(
            $this->getObjectAttribute($api, 'logger')
        );

        $api = new Api($controller, $router);
        $this->assertSame(
            $controller,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertSame(
            $router,
            $this->getObjectAttribute($api, 'router')
        );
        $this->assertNull(
            $this->getObjectAttribute($api, 'logger')
        );

        $api = new Api($controller, $router, $logger);
        $this->assertSame(
            $controller,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertSame(
            $router,
            $this->getObjectAttribute($api, 'router')
        );
        $this->assertSame(
            $logger,
            $this->getObjectAttribute($api, 'logger')
        );
    }

    /**
     * @test
     * @covers ::setLogger
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testSetLogger()
    {
        $controller = new Controller();
        $logger = new NullLogger();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'logger')
        );
        $this->assertSame(
            $api,
            $api->setLogger($logger)
        );
        $this->assertSame(
            $logger,
            $this->getObjectAttribute($api, 'logger')
        );
    }

    /**
     * @test
     * @covers ::log
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setLogger
     */
    public function testLog()
    {
        $controller = new Controller();
        $level = LogLevel::DEBUG;
        $message = 'Test Message';

        /** @var \PHPUnit_Framework_MockObject_MockObject|NullLogger $logger */
        $logger = $this->getMock('\Psr\Log\NullLogger');
        $logger->expects($this->once())
            ->method('log')
            ->with($level, $message, []);

        $api = new Api($controller);
        $log = $this->getObjectMethod($api, 'log');

        $this->assertSame(
            $api,
            $log($level, $message)
        );
        $api->setLogger($logger);

        $this->assertSame(
            $api,
            $log($level, $message)
        );
    }

    /**
     * @test
     * @covers ::setRouter
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testSetRouter()
    {
        $controller = new Controller();
        $router = new Router();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'router')
        );
        $this->assertSame(
            $api,
            $api->setRouter($router)
        );
        $this->assertSame(
            $router,
            $this->getObjectAttribute($api, 'router')
        );
    }

    /**
     * @test
     * @covers ::getRouter
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setRouter
     */
    public function testGetRouter()
    {
        $controller = new Controller();
        $router = new Router();
        $api = new Api($controller);

        $this->assertInstanceOf(
            '\AyeAye\Api\Router',
            $api->getRouter()
        );
        $this->assertNotSame(
            $router,
            $api->getRouter()
        );
        $this->assertSame(
            $api,
            $api->setRouter($router)
        );
        $this->assertSame(
            $router,
            $api->getRouter()
        );
    }

    /**
     * @test
     * @covers ::setInitialController
     * @uses \AyeAye\Api\Api::__construct
     */
    public function testSetInitialController()
    {
        $controllerOne = new Controller();
        $controllerTwo = new Controller();
        $api = new Api($controllerOne);

        $this->assertSame(
            $controllerOne,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertSame(
            $api,
            $api->setInitialController($controllerTwo)
        );
        $this->assertSame(
            $controllerTwo,
            $this->getObjectAttribute($api, 'controller')
        );
    }

    /**
     * @test
     * @covers ::getInitialController
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testGetInitialController()
    {
        $controllerOne = new Controller();
        $controllerTwo = new Controller();
        $api = new Api($controllerOne);

        $this->assertSame(
            $controllerOne,
            $api->getInitialController()
        );
        $this->assertSame(
            $api,
            $api->setInitialController($controllerTwo)
        );
        $this->assertSame(
            $controllerTwo,
            $api->getInitialController()
        );
    }

    /**
     * @test
     * @covers ::setRequest
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Request
     */
    public function testSetRequest()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
        $request = $this->getMock('\AyeAye\Api\Request');
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'request')
        );
        $this->assertSame(
            $api,
            $api->setRequest($request)
        );
        $this->assertSame(
            $request,
            $this->getObjectAttribute($api, 'request')
        );
    }

    /**
     * @test
     * @covers ::getRequest
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setRequest
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequest()
    {
        $controller = new Controller();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
        $request = $this->getMock('\AyeAye\Api\Request');
        $api = new Api($controller);

        $this->assertInstanceOf(
            '\AyeAye\Api\Request',
            $api->getRequest()
        );
        $this->assertNotSame(
            $request,
            $api->getRequest()
        );
        $this->assertSame(
            $api,
            $api->setRequest($request)
        );
        $this->assertSame(
            $request,
            $api->getRequest()
        );
    }

    /**
     * @test
     * @covers ::setResponse
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Response
     */
    public function testSetResponse()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Response $response */
        $response = $this->getMock('\AyeAye\Api\Response');
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'response')
        );
        $this->assertSame(
            $api,
            $api->setResponse($response)
        );
        $this->assertSame(
            $response,
            $this->getObjectAttribute($api, 'response')
        );
    }

    /**
     * @test
     * @covers ::getResponse
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setResponse
     * @uses \AyeAye\Api\Response
     */
    public function testGetResponse()
    {
        $controller = new Controller();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Response $response */
        $response = $this->getMock('\AyeAye\Api\Response');
        $api = new Api($controller);

        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $api->getResponse()
        );
        $this->assertNotSame(
            $response,
            $api->getResponse()
        );
        $this->assertSame(
            $api,
            $api->setResponse($response)
        );
        $this->assertSame(
            $response,
            $api->getResponse()
        );
    }

    /**
     * @test
     * @covers ::setFormatFactory
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testSetFormatFactory()
    {
        $formatFactory = new FormatFactory([]);
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'formatFactory')
        );
        $this->assertSame(
            $api,
            $api->setFormatFactory($formatFactory)
        );
        $this->assertSame(
            $formatFactory,
            $this->getObjectAttribute($api, 'formatFactory')
        );
    }

    /**
     * @test
     * @covers ::getFormatFactory
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setFormatFactory
     */
    public function testGetFormatFactory()
    {
        $formatFactory = new FormatFactory([]);
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertInstanceOf(
            'AyeAye\Formatter\FormatFactory',
            $api->getFormatFactory()
        );
        $this->assertNotSame(
            $formatFactory,
            $api->getFormatFactory()
        );
        $this->assertSame(
            $api,
            $api->setFormatFactory($formatFactory)
        );
        $this->assertSame(
            $formatFactory,
            $this->getObjectAttribute($api, 'formatFactory')
        );
    }

    /**
     * @test
     * @covers ::createFailSafeResponse
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Status
     */
    public function testCreateFailSafeResponse()
    {
        $controller = new Controller();
        $api = new Api($controller);

        $createFailSafeResponse = $this->getObjectMethod($api, 'createFailSafeResponse');

        /** @var Response $response */
        $response = $createFailSafeResponse();
        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $response
        );
        $this->assertSame(
            500,
            $response->getStatus()->getCode()
        );
        $this->assertInstanceOf(
            '\AyeAye\Formatter\Formats\Json',
            $this->getObjectAttribute($response, 'formatter')
        );
    }

}