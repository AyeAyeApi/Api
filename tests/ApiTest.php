<?php
/**
 * ApiTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Api;
use AyeAye\Api\Controller;
use AyeAye\Api\Request;
use AyeAye\Api\Response;
use AyeAye\Api\Router;
use AyeAye\Api\Tests\TestData\ExceptionController;
use AyeAye\Formatter\WriterFactory;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class ApiTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
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
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Api::getRouter
     * @uses \AyeAye\Api\Api::getRequest
     * @uses \AyeAye\Api\Api::getResponse
     * @uses \AyeAye\Api\Api::getWriterFactory
     * @uses \AyeAye\Api\Controller
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Router
     * @uses \AyeAye\Api\Status
     */
    public function testGo()
    {
        $controller = new Controller();
        $api = new Api($controller);
        $response = $api->go();
        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $response
        );
        $this->assertSame(
            200,
            $response->getStatus()->getCode()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Api::getRouter
     * @uses \AyeAye\Api\Api::getRequest
     * @uses \AyeAye\Api\Api::setRequest
     * @uses \AyeAye\Api\Api::getResponse
     * @uses \AyeAye\Api\Api::getWriterFactory
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Controller
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Router
     * @uses \AyeAye\Api\Status
     */
    public function testGoException()
    {
        $controller = new ExceptionController();
        $api = new Api($controller);
        $request = new Request(
            Request::METHOD_GET,
            'exception'
        );
        $api->setRequest($request);
        $response = $api->go();
        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $response
        );
        $this->assertSame(
            500,
            $response->getStatus()->getCode()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Api::getRouter
     * @uses \AyeAye\Api\Api::getRequest
     * @uses \AyeAye\Api\Api::setRequest
     * @uses \AyeAye\Api\Api::getResponse
     * @uses \AyeAye\Api\Api::getWriterFactory
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Controller
     * @uses \AyeAye\Api\Exception
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Router
     * @uses \AyeAye\Api\Status
     */
    public function testGoAyeAyeException()
    {
        $controller = new ExceptionController();
        $api = new Api($controller);
        $request = new Request(
            Request::METHOD_GET,
            'aye-aye-exception'
        );
        $api->setRequest($request);
        $response = $api->go();
        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $response
        );
        $this->assertSame(
            418,
            $response->getStatus()->getCode()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Api::getRouter
     * @uses \AyeAye\Api\Api::getRequest
     * @uses \AyeAye\Api\Api::getResponse
     * @uses \AyeAye\Api\Api::setResponse
     * @uses \AyeAye\Api\Api::getWriterFactory
     * @uses \AyeAye\Api\Api::createFailSafeResponse
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Controller
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Router
     * @uses \AyeAye\Api\Status
     */
    public function testGoResponseException()
    {
        $responseBase = $this->getMock('\AyeAye\Api\Response');
        $responseBase->expects($this->once())
            ->method('prepareResponse')
            ->with()
            ->will($this->throwException(new \Exception()));
        $controller = new Controller();
        $api = new Api($controller);
        $api->setResponse($responseBase);
        $response = $api->go();
        $this->assertInstanceOf(
            '\AyeAye\Api\Response',
            $response
        );
        $this->assertSame(
            500,
            $response->getStatus()->getCode()
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
     * @covers ::setWriterFactory
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testSetFormatFactory()
    {
        $writerFactory = new WriterFactory([]);
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertNull(
            $this->getObjectAttribute($api, 'writerFactory')
        );
        $this->assertSame(
            $api,
            $api->setWriterFactory($writerFactory)
        );
        $this->assertSame(
            $writerFactory,
            $this->getObjectAttribute($api, 'writerFactory')
        );
    }

    /**
     * @test
     * @covers ::getWriterFactory
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::setWriterFactory
     */
    public function testGetWriterFactory()
    {
        $writerFactory = new WriterFactory([]);
        $controller = new Controller();
        $api = new Api($controller);

        $this->assertInstanceOf(
            'AyeAye\Formatter\WriterFactory',
            $api->getWriterFactory()
        );
        $this->assertNotSame(
            $writerFactory,
            $api->getWriterFactory()
        );
        $this->assertSame(
            $api,
            $api->setWriterFactory($writerFactory)
        );
        $this->assertSame(
            $writerFactory,
            $this->getObjectAttribute($api, 'writerFactory')
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
            '\AyeAye\Formatter\Writer\Json',
            $this->getObjectAttribute($response, 'writer')
        );
    }
}
