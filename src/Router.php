<?php
/**
 * Router.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\ControllerReflectorInjector;
use AyeAye\Api\Injector\StatusInjector;

/**
 * Class Router
 * Finds the correct endpoint to process a request
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Router
{
    use ControllerReflectorInjector;
    use StatusInjector;

    /**
     * Look at a request and work out what to do next.
     *
     * Looks at the request chain, takes the first part off, if there is a
     * matching controller, it will load the controller and recurse. If there
     * is a matching endpoint it will call the endpoint passing in the
     * request data.
     *
     * @param Request $request
     * @param Controller $controller
     * @param array $requestChain
     * @return mixed
     * @throws Exception
     */
    public function processRequest(Request $request, Controller $controller, array $requestChain = null)
    {

        $reflectionController = $this->getControllerReflector()->reflectController($controller);

        // If the request chain is null as apposed to empty, we can get it from the request
        if (is_null($requestChain)) {
            $requestChain = $request->getRequestChain();
        }

        // Get the next element of the front of the chain
        $nextLink = array_shift($requestChain);
        if ($nextLink) {
            // If the next element represents a controller, recurse with the new controller and remaining chain
            if ($reflectionController->hasChildController($nextLink)) {
                return $this->processRequest(
                    $request,
                    $reflectionController->getChildController($nextLink),
                    $requestChain
                );
            }

            // If the next element represents an endpoint, call it, passing in the request data
            if ($reflectionController->hasEndpoint($request->getMethod(), $nextLink)) {
                $data = $reflectionController->getEndpointResult($request->getMethod(), $nextLink, $request);
                $this->setStatus($reflectionController->getStatus());
                return $data;
            }

            // If we had another element but it doesn't represent anything, throw an exception.
            $message = "Could not find controller or endpoint matching '$nextLink'";
            throw new Exception($message, 404);
        }

        // If there were no more links in the chain, call the index endpoint if there is one.
        // Index endpoints will prevent automated automatic documentation, which may be undesirable. See below.
        if ($reflectionController->hasEndpoint($request->getMethod(), 'index')) {
            $data = $reflectionController->getEndpointResult($request->getMethod(), 'index', $request);
            $this->setStatus($reflectionController->getStatus());
            return $data;
        }

        // Generate documentation for the current controller and return it.
        return $reflectionController->getDocumentation();
    }
}
