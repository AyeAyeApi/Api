<?php
/**
 * ApiTest.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Api;
use AyeAye\Api\Response;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\Injector\RequestInjectorTest;
use AyeAye\Api\Tests\Injector\ResponseInjectorTest;
use AyeAye\Api\Tests\Injector\RouterInjectorTest;
use AyeAye\Api\Tests\Injector\WriterFactorInjectorTest;
use AyeAye\Api\Tests\Injector\LoggerInjectorTest;
use Psr\Log\LogLevel;

/**
 * Class ApiTest
 * @package AyeAye\Api\Tests
 * @see     https://github.com/AyeAyeApi/Api
 * @coversDefaultClass \AyeAye\Api\Api
 */
class ApiTest extends TestCase
{

    use LoggerInjectorTest;
    use RequestInjectorTest;
    use ResponseInjectorTest;
    use RouterInjectorTest;
    use WriterFactorInjectorTest;

    /**
     * @return Api
     */
    protected function getTestSubject()
    {
        $controller    = $this->getMockController();
        return new Api($controller);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function testConstruct()
    {
        // Mocks
        $controller = $this->getMockController();

        // Tests
        $api = new Api($controller);

        $this->assertSame(
            $controller,
            $this->getObjectAttribute($api, 'controller')
        );
    }

    /**
     * @test
     * @covers ::log
     * @uses \AyeAye\Api\Api::__construct
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
        $logger = $this->getMockLogger();

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
        $controller = $this->getMockController();
        $request = $this->getMockRequest();
        $response = $this->getMockResponse();
        $router = $this->getMockRouter();
        $logger = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $status = $this->getMockStatus();

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
        $api = new Api($controller);
        $api->setRouter($router)
            ->setLogger($logger)
            ->setRequest($request)
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
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     */
    public function testGoAyeAyeException()
    {
        // Test Data
        $code = 500;
        $publicMessage = 'public message';
        $privateMessage = 'private message';


        // Mocks
        $controller = $this->getMockController();
        $request = $this->getMockRequest();
        $response = $this->getMockResponse();
        $logger = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $exception = $this->getMockAyeAyeException();

        $exception
            ->expects($this->exactly(2))
            ->method('getPublicMessage')
            ->will($this->returnValue($publicMessage));
        $this->setObjectAttribute($exception, 'message', $privateMessage);
        $this->setObjectAttribute($exception, 'code', $code);

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory)
            ->willThrowException($exception);
        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with($this->isInstanceOf(Status::class));
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
        $api = new Api($controller);
        $api->setLogger($logger)
            ->setRequest($request)
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
        $controller = $this->getMockController();
        $request = $this->getMockRequest();
        $response = $this->getMockResponse();
        $logger = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();

        $response
            ->expects($this->once())
            ->method('setWriterFactory')
            ->with($writerFactory)
            ->willThrowException($exception);
        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with($this->isInstanceOf(Status::class));
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
        $api = new Api($controller);
        $api->setLogger($logger)
            ->setRequest($request)
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
     * @uses \AyeAye\Api\Injector\LoggerInjector
     * @uses \AyeAye\Api\Injector\ResponseInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Api\Injector\RouterInjector
     * @uses \AyeAye\Api\Injector\StatusInjector
     * @uses \AyeAye\Api\Injector\WriterFactoryInjector
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Formatter\Writer\Json
     */
    public function testGoFailSafe()
    {
        // Test Data
        $data = 'data';
        $message = 'message';
        $exception = new \Exception($message);

        // Mocks
        $controller = $this->getMockController();
        $request = $this->getMockRequest();
        $response = $this->getMockResponse();
        $router = $this->getMockRouter();
        $logger = $this->getMockLogger();
        $writerFactory = $this->getMockWriterFactory();
        $status = $this->getMockStatus();

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
        $api = new Api($controller);
        $api->setRouter($router)
            ->setLogger($logger)
            ->setRequest($request)
            ->setResponse($response)
            ->setWriterFactory($writerFactory);

        $this->assertNotSame(
            $response,
            $api->go()
        );
    }

    /**
     * @test
     * @covers ::createFailSafeResponse
     * @uses \AyeAye\Api\Api::__construct
     * @uses \AyeAye\Api\Status
     * @uses \AyeAye\Api\Response
     * @uses \AyeAye\Api\Request
     * @uses \AyeAye\Api\Injector\StatusInjector
     * @uses \AyeAye\Api\Injector\RequestInjector
     * @uses \AyeAye\Formatter\Writer\Json
     */
    public function testCreateFailSafeResponse()
    {
        // Mocks
        $controller = $this->getMockController();

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
