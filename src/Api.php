<?php
/**
 * Api.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer\Json;
use AyeAye\Formatter\Writer\Xml;
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

    /**
     * The starting controller for the Api
     * @var Controller
     */
    protected $controller;

    /**
     * The router that will direct the request
     * @var Router
     */
    protected $router;

    /**
     * The request object to use for this call
     * @var Request
     */
    protected $request;

    /**
     * The response object to return for this call
     * @var Response
     */
    protected $response;

    /**
     * A collection of writers available
     * @var WriterFactory
     */
    protected $writerFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
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
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
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
     * Set the router to be used when go is called
     * @param Router $router
     * @return $this;
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get the router
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->router) {
            $this->router = new Router();
        }
        return $this->router;
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
     * Set the request object. Use for dependency injection
     * @param Request $request
     * @return $this;
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the request. If none is set it will create a default Request object
     * @return Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     * Set the response object. Use for dependency injection
     * @param Response $response
     * @returns $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the response object. If none is set it will create a default Response object
     * @return Response
     */
    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new Response();
        }
        return $this->response;
    }

    /**
     * Sets the format factory. Use for dependency injection, or additional formatters
     * @param WriterFactory $writerFactory
     * @returns $this
     */
    public function setWriterFactory(WriterFactory $writerFactory)
    {
        $this->writerFactory = $writerFactory;
        return $this;
    }

    /**
     * Get the format factory. If none is set it will create a default format factory for xml and json
     * @return WriterFactory
     */
    public function getWriterFactory()
    {
        if (!$this->writerFactory) {
            $xmlFormatter = new Xml();
            $jsonFormatter = new Json();
            $this->writerFactory = new WriterFactory([
                // xml
                'xml' => $xmlFormatter,
                'text/xml' => $xmlFormatter,
                'application/xml' => $xmlFormatter,
                // json
                'json' => $jsonFormatter,
                'application/json' => $jsonFormatter,
            ]);
        }
        return $this->writerFactory;
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
