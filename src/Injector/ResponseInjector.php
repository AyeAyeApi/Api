<?php

namespace AyeAye\Api\Injector;

use AyeAye\Api\Response;

trait ResponseInjector
{

    /**
     * The response object to return for this call
     * @var Response
     */
    private $response;

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
}
