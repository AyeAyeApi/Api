<?php
/**
 * ResponseInjector.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\Response;

/**
 * Trait ResponseInjector
 * Allows the injection and management of a Response object. Provides a default if one isn't set.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait ResponseInjector
{
    /**
     * The response object to return for this call
     * @var Response
     */
    private $response;

    /**
     * Set the response object.
     *
     * Use for dependency injection.
     *
     * @param Response $response
     * @returns $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the response object.
     *
     * If none is set it will create a default Response object.
     *
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
