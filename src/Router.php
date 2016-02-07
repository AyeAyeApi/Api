<?php
/**
 * Router.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\ControllerReflectorInjector;
use AyeAye\Api\Injector\StatusInjector;

/**
 * Class Router
 * Finds the correct endpoint to process a request and parses in the request data
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Router
{
    use ControllerReflectorInjector;
    use StatusInjector;

    /**
     * Look at a request and work out what to do next.
     * Call a controller, or an endpoint on this controller.
     * @param Request $request
     * @param Controller $controller
     * @param array $requestChain
     * @return mixed
     * @throws Exception
     */
    public function processRequest(Request $request, Controller $controller, array $requestChain = null)
    {

        $reflectionController = $this->getControllerReflector()->reflectController($controller);

        if (is_null($requestChain)) {
            $requestChain = $request->getRequestChain();
        }

        $nextLink = array_shift($requestChain);
        if ($nextLink) {
            if ($reflectionController->hasChildController($nextLink)) {
                return $this->processRequest(
                    $request,
                    $reflectionController->getChildController($nextLink),
                    $requestChain
                );
            }

            if ($reflectionController->hasEndpoint($request->getMethod(), $nextLink)) {
                $data = $reflectionController->getEndpointResult($request->getMethod(), $nextLink, $request);
                $this->setStatus($reflectionController->getStatus());
                return $data;
            }

            $message = "Could not find controller or endpoint matching '$nextLink'";
            throw new Exception($message, 404);
        }

        if ($reflectionController->hasEndpoint($request->getMethod(), 'index')) {
            return $reflectionController->getEndpointResult($request->getMethod(), 'index', $request);
        }

        return $reflectionController->getDocumentation();
    }
}
