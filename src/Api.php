<?php
/**
 * Api.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\LoggerInjector;
use AyeAye\Api\Injector\RequestInjector;
use AyeAye\Api\Injector\ResponseInjector;
use AyeAye\Api\Injector\RouterInjector;
use AyeAye\Api\Injector\WriterFactoryInjector;
use AyeAye\Formatter\Writer\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Api
 * Quick start your API with this class
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Api implements LoggerAwareInterface
{
    use LoggerInjector;
    use RequestInjector;
    use ResponseInjector;
    use RouterInjector;
    use WriterFactoryInjector;

    /**
     * The starting controller for the Api
     * @var Controller
     */
    protected $controller;

    /**
     * Initialise the API with a controller that forms the starting point of routing information
     * @param Router $router The router to power the api
     * @param Controller $initialController The starting point for the Api
     * @param LoggerInterface $logger Provide a logger
     */
    public function __construct(Controller $initialController, Router $router = null, LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }
        $this->setInitialController($initialController);
        if ($router) {
            $this->setRouter($router);
        }
    }

    /**
     * Write to the pre-set logger
     * @param $level
     * @param $message
     * @param array $context
     * @return $this
     */
    protected function log($level, $message, array $context = array())
    {
        $this->getLogger()->log($level, $message, $context);
        return $this;
    }

    /**
     * Process the request, get a response and return it.
     * Exceptions thrown in most places will be handled here, though currently there's no way to handle exceptions
     * int the Response object itself (eg, invalid formats)e
     * Tip. You can ->respond() straight off this method
     * @return Response
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function go()
    {
        $response = $this->getResponse();

        try {
            $request = $this->getRequest();
            $response->setWriterFactory(
                $this->getWriterFactory()
            );
            $response->setRequest(
                $request
            );
            $response->setBodyData(
                $this->getRouter()->processRequest(
                    $this->getRequest(),
                    $this->getInitialController()
                )
            );
            $response->setStatus(
                $this->controller->getStatus()
            );
        } catch (Exception $e) {
            $this->log(LogLevel::INFO, $e->getPublicMessage());
            $this->log(LogLevel::ERROR, $e->getMessage(), ['exception' => $e]);
            $response->setBodyData($e->getPublicMessage());
            $response->setStatusCode($e->getCode());
        } catch (\Exception $e) {
            $status = new Status(500);
            $this->log(LogLevel::CRITICAL, $e->getMessage(), ['exception' => $e]);
            $response->setBodyData($status->getMessage());
            $response->setStatusCode($status->getCode());
        }

        // Ultimate fail safe
        try {
            $response->prepareResponse();
        } catch (\Exception $e) {
            $this->log(LogLevel::CRITICAL, $e->getMessage(), ['exception' => $e]);
            return $this->createFailSafeResponse();
        }

        return $response;
    }

    /**
     * Set the initial controller that the api will begin with
     * @param Controller $controller
     * @returns $this
     */
    public function setInitialController(Controller $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get the initial controller that the api will begin with
     * @return Controller
     */
    public function getInitialController()
    {
        return $this->controller;
    }

    /**
     * In the event of a catastrophic failure, this response can be used to return JSON
     * @return Response
     */
    protected function createFailSafeResponse()
    {
        $status = new Status(500);
        $response = new Response();
        $response->setRequest(new Request());
        $response->setWriter(new Json());
        $response->setStatus($status);
        $response->setBodyData($status->getMessage());
        return $response;
    }
}
