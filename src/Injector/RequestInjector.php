<?php
/**
 * RequestInjector.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\Request;

/**
 * Trait RequestInjector
 * Allows the injection and management of a Request object. Provides a default if one isn't set.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait RequestInjector
{
    /**
     * The request object to use for this call
     * @var Request
     */
    private $request;

    /**
     * Set the request object.
     *
     * Use for injecting or overriding the actual request from the client.
     *
     * @param Request $request
     * @return $this;
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the request object.
     *
     * If none is set it will create a default Request object that makes
     * available all of the parameters Aye Aye can find in the actual request
     * from the user.
     *
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
