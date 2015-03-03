<?php
/**
 * Quick start your API with this class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

use AyeAye\Formatter\FormatFactory;
use AyeAye\Formatter\Formats\Json;
use AyeAye\Formatter\Formats\Xml;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Used to wrap the other classes into easier to manage code
 * @package AyeAye\Api
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
     * A collection of formatters available
     * @var FormatFactory
     */
    protected $formatFactory;

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
        if($logger) {
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
     */
    protected function log($level, $message, array $context = array())
    {
        if($this->logger) {
            $this->logger->log($level, $message, $context);
        }
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
            $response->setFormatFactory(
                $this->getFormatFactory()
            );
            $response->setRequest(
                $request
            );
            $response->setData(
                $this->getRouter()->processRequest(
                    $this->getRequest(),
                    $this->getInitialController()
                )
            );
            $response->setStatus(
                $this->controller->getStatus()
            );
            return $response;
        } catch (Exception $e) {
            $this->log(LogLevel::ERROR, $e->getMessage(), ['exception' => $e]);
            $response->setData($e->getPublicMessage());
            $response->setStatusCode($e->getCode());
            return $response;
        } catch (\Exception $e) {
            $this->log(LogLevel::CRITICAL, $e->getMessage(), ['exception' => $e]);
            $response->setData(Status::getMessageForCode(500));
            $response->setStatusCode($e->getCode());
            return $response;
        }
    }

    /**
     * Set the router to be used when go is called
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
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
     */
    public function setInitialController(Controller $controller)
    {
        $this->controller = $controller;
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
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
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
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
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
     * @param FormatFactory $formatFactory
     */
    public function setFormatFactory(FormatFactory $formatFactory)
    {
        $this->formatFactory = $formatFactory;
    }

    /**
     * Get the format factory. If none is set it will create a default format factory for xml and json
     * @return FormatFactory
     */
    public function getFormatFactory()
    {
        if (!$this->formatFactory) {
            $xmlFormatter = new Xml();
            $jsonFormatter = new Json();
            $this->formatFactory = new FormatFactory([
                // xml
                'xml' => $xmlFormatter,
                'text/xml' => $xmlFormatter,
                'application/xml' => $xmlFormatter,
                // json
                'json' => $jsonFormatter,
                'application/json' => $jsonFormatter,
            ]);
        }
        return $this->formatFactory;
    }
}
