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
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use Psr\Log\LogLevel;

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
     */
    public function testConstructController()
    {
        // Mocks
        $controller = $this->getMockController();

        // Tests
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
    }

    /**
     * @test
     * @covers ::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Injector\RouterInjector
     */
    public function testConstructControllerRouter()
    {
        // Mocks
        $controller = $this->getMockController();
        $router     = $this->getMockRouter();

        // Tests
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
    }

    /**
     * @test
     * @covers ::__construct
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testConstructControllerLogger()
    {
        // Mocks
        $controller = $this->getMockController();
        $logger     = $this->getMockLogger();


        // Tests
        $api = new Api($controller, null, $logger);

        $this->assertSame(
            $controller,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertNull(
            $this->getObjectAttribute($api, 'router')
        );
        $this->assertSame(
            $logger,
            $this->getObjectAttribute($api, 'logger')
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\RouterInjector
     */
    public function testConstructAll()
    {
        // Mocks
        $controller = $this->getMockController();
        $router     = $this->getMockRouter();
        $logger     = $this->getMockLogger();

        // Tests
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
        // Mocks
        $controller = $this->getMockController();
        $logger     = $this->getMockLogger();

        // Tests
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
     * @uses \AyeAye\Api\Injector\LoggerInjector
     */
    public function testLog()
    {

        // Data
        $level0 = 'level0';
        $message0 = 'message0';

        $level1 = 'level1';
        $message1 = 'message1';
        $context1 = ['context' => '1'];

        $level2 = 'level2';
        $message2 = 'message2';

        // Mocks
        $controller = $this->getMockController();
        $logger     = $this->getMockLogger();

        $logger
            ->expects($this->at(0))
            ->method('log')
            ->with($level1, $message1, $context1);
        $logger
            ->expects($this->at(1))
            ->method('log')
            ->with($level2, $message2);

        // Tests
        $api = new Api($controller);
        $log = $this->getObjectMethod($api, 'log');

        // Not logged
        $this->assertSame(
            $api,
            $log($level0, $message0)
        );

        // Set Logger
        $this->assertSame(
            $api,
            $api->setLogger($logger)
        );

        // Log twice
        $this->assertSame(
            $api,
            $log($level1, $message1, $context1)
        );

        $this->assertSame(
            $api,
            $log($level2, $message2)
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\RouterInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     */
    public function testGo()
    {
        // Test Data
        $data = 'data';

        // Mocks
        $controller    = $this->getMockController();
        $request       = $this->getMockRequest();
        $response      = $this->getMockResponse();
        $router        = $this->getMockRouter();
        $logger        = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $status        = $this->getMockStatus();

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory);
        $response
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $response
            ->expects($this->once())
            ->method('setBodyData')
            ->with($data);
        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with($status);
        $response
            ->expects($this->once())
            ->method('prepareResponse');

        $router
            ->expects($this->once())
            ->method('processRequest')
            ->with($request, $controller)
            ->will($this->returnValue($data));

        $controller
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($status));

        // Tests
        $api = new Api($controller, $router, $logger);
        $api->setRequest($request)
            ->setResponse($response)
            ->setWriterFactory($writerFactory);

        $this->assertSame(
            $response,
            $api->go()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     */
    public function testGoAyeAyeException()
    {
        // Test Data
        $code = 500;
        $publicMessage  = 'public message';
        $privateMessage = 'private message';


        // Mocks
        $controller    = $this->getMockController();
        $request       = $this->getMockRequest();
        $response      = $this->getMockResponse();
        $logger        = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $exception     = $this->getMockAyeAyeException();

        $exception
            ->expects($this->exactly(2))
            ->method('getPublicMessage')
            ->will($this->returnValue($publicMessage));
        $this->setObjectAttribute($exception, 'message', $privateMessage);
        $this->setObjectAttribute($exception, 'code',    $code);

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory)
            ->willThrowException($exception);
        $response
            ->expects($this->once())
            ->method('setStatusCode')
            ->with($code);
        $response
            ->expects($this->once())
            ->method('prepareResponse');
        $response
            ->expects($this->once())
            ->method('setBodyData')
            ->with($publicMessage);

        $logger
            ->expects($this->at(0))
            ->method('log')
            ->with(LogLevel::INFO, $publicMessage);
        $logger
            ->expects($this->at(1))
            ->method('log')
            ->with(LogLevel::ERROR, $privateMessage, ['exception' => $exception]);



        // Tests
        $api = new Api($controller, null, $logger);
        $api->setRequest($request)
            ->setResponse($response)
            ->setWriterFactory($writerFactory);

        $this->assertSame(
            $response,
            $api->go()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     * @uses \AyeAye\Api\Status
     */
    public function testGoException()
    {
        // Test Data
        $message = 'message';
        $exception = new \Exception($message);

        // Mocks
        $controller    = $this->getMockController();
        $request       = $this->getMockRequest();
        $response      = $this->getMockResponse();
        $logger        = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory)
            ->willThrowException($exception);
        $response
            ->expects($this->once())
            ->method('setStatusCode')
            ->with(500);
        $response
            ->expects($this->once())
            ->method('prepareResponse');
        $response
            ->expects($this->once())
            ->method('setBodyData')
            ->with('Internal Server Error');

        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::CRITICAL, $message, ['exception' => $exception]);



        // Tests
        $api = new Api($controller, null, $logger);
        $api->setRequest($request)
            ->setResponse($response)
            ->setWriterFactory($writerFactory);

        $this->assertSame(
            $response,
            $api->go()
        );
    }

    /**
     * @test
     * @covers ::go
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::createFailSafeResponse
     * @uses \AyeAye\Api\Api::log
     * @uses \AyeAye\Api\Api::getInitialController
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\RouterInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Formatter\Writer\Json
     */
    public function testGoFailSafe()
    {
        // Test Data
        $data      = 'data';
        $message   = 'message';
        $exception = new \Exception($message);

        // Mocks
        $controller    = $this->getMockController();
        $request       = $this->getMockRequest();
        $response      = $this->getMockResponse();
        $router        = $this->getMockRouter();
        $logger        = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $status        = $this->getMockStatus();

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory);
        $response
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $response
            ->expects($this->once())
            ->method('setBodyData')
            ->with($data);
        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with($status);
        $response
            ->expects($this->once())
            ->method('prepareResponse')
            ->willThrowException($exception);

        $router
            ->expects($this->once())
            ->method('processRequest')
            ->with($request, $controller)
            ->will($this->returnValue($data));

        $controller
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($status));

        $logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::CRITICAL, $message, ['exception' => $exception]);

        // Tests
        $api = new Api($controller, $router, $logger);
        $api->setRequest($request)
            ->setResponse($response)
            ->setWriterFactory($writerFactory);

        $this->assertNotSame(
            $response,
            $api->go()
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
        // Mocks
        $controller = $this->getMockController();
        $router     = $this->getMockRouter();

        // Tests
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
     * @uses \AyeAye\Api\Injector\RouterInjector
     */
    public function testGetRouter()
    {
        // Mocks
        $controller = $this->getMockController();
        $router     = $this->getMockRouter();

        // Tests
        $api = new Api($controller);

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
    public function testInitialController()
    {
        // Mocks
        $controller1 = $this->getMockController();
        $controller2 = $this->getMockController();

        // Tests
        $api = new Api($controller1);

        $this->assertSame(
            $controller1,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertNotSame(
            $controller2,
            $this->getObjectAttribute($api, 'controller')
        );

        $this->assertSame(
            $api,
            $api->setInitialController($controller2)
        );

        $this->assertSame(
            $controller2,
            $this->getObjectAttribute($api, 'controller')
        );
        $this->assertNotSame(
            $controller1,
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
        // Mocks
        $controller1 = $this->getMockController();
        $controller2 = $this->getMockController();


        // Tests
        $api = new Api($controller1);

        $this->assertSame(
            $controller1,
            $api->getInitialController()
        );
        $this->assertNotSame(
            $controller2,
            $api->getInitialController()
        );

        $this->assertSame(
            $api,
            $api->setInitialController($controller2)
        );

        $this->assertSame(
            $controller2,
            $api->getInitialController()
        );
        $this->assertNotSame(
            $controller1,
            $api->getInitialController()
        );
    }

    /**
     * @test
     * @covers ::setRequest
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     */
    public function testSetRequest()
    {
        // Mocks
        $controller = $this->getMockController();
        $request    = $this->getMockRequest();


        // Tests
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
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequest()
    {
        // Mocks
        $controller = $this->getMockController();
        $request    = $this->getMockRequest();

        // Tests
        $api = new Api($controller);

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
     */
    public function testSetResponse()
    {
        // Mocks
        $controller = $this->getMockController();
        $response   = $this->getMockResponse();

        // Tests
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
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Response
     */
    public function testGetResponse()
    {
        // Mocks
        $controller = $this->getMockController();
        $response   = $this->getMockResponse();

        // Tests
        $api = new Api($controller);

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
    public function testSetWriterFactory()
    {
        // Mocks
        $controller    = $this->getMockController();
        $writerFactory = $this->getMockWriterFactory();

        // Tests
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
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     * @uses \AyeAye\Formatter\WriterFactory
     */
    public function testGetWriterFactory()
    {
        // Test Data
        $requiredFormats = [
            'xml',
            'text/xml',
            'application/xml',
            'json',
            'application/json',
        ];

        // Mocks
        $controller    = $this->getMockController();
        $writerFactory = $this->getMockWriterFactory();

        // Tests
        $api = new Api($controller);

        $this->assertNotSame(
            $writerFactory,
            $api->getWriterFactory()
        );

        // Test the factory was constructed correctly
        $formats = $this->getObjectAttribute($api->getWriterFactory(), 'formats');
        foreach($requiredFormats as $format) {
            $this->assertArrayHasKey(
                $format,
                $formats
            );
        }

        $this->assertSame(
            $api,
            $api->setWriterFactory($writerFactory)
        );

        $this->assertSame(
            $writerFactory,
            $api->getWriterFactory()
        );
    }

    /**
     * @test
     * @covers ::createFailSafeResponse
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Api::setInitialController
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Formatter\Writer\Json
     */
    public function testCreateFailSafeResponse()
    {
        // Mocks
        $controller    = $this->getMockController();

        // Tests
        $api = new Api($controller);

        $createFailSafeResponse = $this->getObjectMethod($api, 'createFailSafeResponse');

        /** @var Response $response */
        $response = $createFailSafeResponse();

        $this->assertSame(
            500,
            $response->getStatus()->getCode()
        );
        $this->assertSame(
            Status::getMessageForCode(500),
            $response->getBody()['data']
        );
    }

}
