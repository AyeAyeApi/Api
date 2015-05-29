<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 03/03/2015
 * Time: 20:12
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Api;
use AyeAye\Api\Request;
use AyeAye\Api\Status;
use AyeAye\Api\Tests\TestData\FailSafeController;
use AyeAye\Api\Tests\TestData\FailSafeFormatter;
use AyeAye\Api\Tests\TestData\TestController;
use AyeAye\Api\Tests\TestData\TestLogger;
use AyeAye\Formatter\FormatFactory;
use Psr\Log\LogLevel;

class FailSafeTest extends TestCase
{

    public function testApiLog()
    {
        $logger = new TestLogger();
        $controller = new TestController();
        $api = new Api($controller, null, $logger);
        $log = $this->getClassMethod($api, 'log');
        $log->invoke($api, LogLevel::INFO, 'Hello World');
        $this->assertSame(
            1,
            $logger->countLogs()
        );
    }

    public function testAyeAyeException()
    {
        $logger = new TestLogger();
        $controller = new FailSafeController();
        $api = new Api($controller, null, $logger);
        $request = new Request(Request::METHOD_GET, 'aye-aye-exception');
        $response = $api->setRequest($request)->go();
        $this->assertSame(
            FailSafeController::PUBLIC_MESSAGE,
            $response->getData()
        );

        $this->assertTrue(
            $logger->wasLogged(FailSafeController::SYSTEM_MESSAGE)
        );
        $this->assertTrue(
            $logger->wasLogged(FailSafeController::PUBLIC_MESSAGE)
        );
    }

    public function testBasicException()
    {
        $logger = new TestLogger();
        $controller = new FailSafeController();
        $status = new Status(500);
        $api = new Api($controller, null, $logger);
        $request = new Request(Request::METHOD_GET, 'basic-exception');
        $response = $api->setRequest($request)->go();
        $this->assertSame(
            $status->getMessage(),
            $response->getData()
        );

        $this->assertTrue(
            $logger->wasLogged(FailSafeController::SYSTEM_MESSAGE)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testDuffFormatter()
    {
        $logger = new TestLogger();
        $controller = new TestController();
        $status = new Status(500);
        $api = new Api($controller, null, $logger);
        $formatFactory = new FormatFactory(['json' => new FailSafeFormatter()]);
        $api->setFormatFactory($formatFactory);

        ob_start();
        $api->go()->respond();
        $result = ob_get_contents();
        ob_end_clean();


        $this->assertNotFalse(
            strpos($result, $status->getMessage())
        );

        $this->assertTrue(
            $logger->wasLogged(FailSafeController::SYSTEM_MESSAGE)
        );
    }
}
