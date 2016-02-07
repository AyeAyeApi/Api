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

/**
 * Class Router
 * Finds the correct endpoint to process a request and parses in the request data
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Router
{
    use ControllerReflectorInjector;

    /**
     * The status object that represents an HTTP status
     * @var Status
     */
    protected $status;

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

    /**
     * Get the Status object associated with the controller
     * @return Status
     */
    public function getStatus()
    {
        if (!$this->status) {
            $this->status = new Status();
        }
        return $this->status;
    }

    /**
     * Set the status object associated with the controller
     * @param Status $status
     * @return $this
     */
    protected function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }
}
