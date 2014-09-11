<?php
/**
 * Quick start your API with this class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;

use Gisleburt\Formatter\FormatFactory;

class Api {

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var FormatFactory
     */
    protected $formatFactory;


    public function __construct(Controller $initialController) {
        $this->controller = $initialController;
    }

    /**
     * Process the request, get a response and return it.
     * Tip. You can ->respond() straight off this method
     * @return Response
     */
    public function go() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $response->setFormatFactory(
            $this->getFormatFactory()
        );
        $response->setRequest(
            $request
        );
        $response->setData(
            $this->controller->processRequest($request)
        );
        $response->setStatus(
            $this->controller->getStatus()
        );
        return $response;
    }

    /**
     * Set the request object. Use for dependency injection
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Get the request. If none is set it will create a default Request object
     * @return Request
     */
    public function getRequest() {
        if(!$this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     * Set the response object. Use for dependency injection
     * @param Response $response
     */
    public function setResponse(Response $response) {
        $this->response = $response;
    }

    /**
     * Get the response object. If none is set it will create a default Response object
     * @return Response
     */
    public function getResponse() {
        if(!$this->response) {
            $this->response = new Response();
        }
        return $this->response;
    }

    /**
     * Sets the format factory. Use for dependency injection, or additional formatters
     * @param FormatFactory $formatFactory
     */
    public function setFormatFactory(FormatFactory $formatFactory) {
        $this->formatFactory = $formatFactory;
    }

    /**
     * Get the format factory. If none is set it will create a default format factory for xml and json
     * @return FormatFactory
     */
    public function getFormatFactory() {
        if(!$this->formatFactory) {
            $this->formatFactory = new FormatFactory([
                'xml' => 'Gisleburt\Formatter\Formats\Xml',
                'json' => 'Gisleburt\Formatter\Formats\Json',
            ]);
        }
        return $this->formatFactory;
    }

}