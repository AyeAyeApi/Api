<?php

namespace AyeAye\Api\Injector;

use AyeAye\Api\Request;

trait RequestInjector
{
    /**
     * The request object to use for this call
     * @var Request
     */
    private $request;

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
}
