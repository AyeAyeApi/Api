<?php
/**
 * Router.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\Deserializable;

/**
 * Class Router
 * Finds the correct endpoint to process a request and parses in the request data
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Router
{

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

        if (is_null($requestChain)) {
            $requestChain = $request->getRequestChain();
        }

        $nextLink = array_shift($requestChain);
        if ($nextLink) {
            $potentialController = $this->parseControllerName($nextLink);
            if (method_exists($controller, $potentialController)) {
                /** @var Controller $nextController */
                $nextController = $controller->$potentialController();
                return $this->processRequest($request, $nextController, $requestChain);
            }

            $potentialEndpoint = $this->parseEndpointName($nextLink, $request->getMethod());
            if (method_exists($controller, $potentialEndpoint)) {
                $data = call_user_func_array(
                    [$controller, $potentialEndpoint],
                    $this->getParametersFromRequest($request, $controller, $potentialEndpoint)
                );
                $this->setStatus($controller->getStatus());
                return $data;
            }

            $message = "Could not find controller or endpoint matching '$nextLink'";
            throw new Exception($message, 404);
        }

        $potentialEndpoint = $this->parseEndpointName('index', $request->getMethod());
        if (method_exists($controller, $potentialEndpoint)) {
            return $controller->$potentialEndpoint();
        }

        return $this->documentController($controller);

    }

    /**
     * Returns a list of possible endpoints and controllers
     * @param Controller $controller
     * @return \stdClass
     */
    protected function documentController(Controller $controller)
    {
        $data = new \stdClass();
        $data->controllers = $this->getControllers($controller);
        $data->endpoints = $this->getEndpoints($controller);
        return $data;
    }

    /**
     * Takes a camelcase string, such as method names, and hyphenates it for urls
     * @param string $camelcaseString
     * @return string Hyphenated string for urls
     */
    protected function camelcaseToHyphenated($camelcaseString)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/s', '$1-$2', $camelcaseString));
    }

    /**
     * Returns a list of endpoints attached to this class
     * @param Controller $controller
     * @return array
     */
    protected function getEndpoints(Controller $controller)
    {
        $documentation = new Documentation();
        $endpoints = [];
        $parts = [];
        $methods = get_class_methods($controller);
        foreach ($methods as $classMethod) {
            if (preg_match('/([a-z]+)([A-Z]\w+)Endpoint$/', $classMethod, $parts)) {
                if (!$controller->isMethodHidden($classMethod)) {
                    $method = strtolower($parts[1]);
                    $endpoint = $this->camelcaseToHyphenated($parts[2]);
                    if (!array_key_exists($method, $endpoints)) {
                        $endpoints[$method] = array();
                    }
                    $endpoints[$method][$endpoint] = $documentation->getMethodDocumentation(
                        new \ReflectionMethod($controller, $classMethod)
                    );
                }
            }
        }
        return $endpoints;
    }

    /**
     * Returns a list of controllers attached to this class
     * @param Controller $controller
     * @return array
     */
    protected function getControllers(Controller $controller)
    {
        $methods = get_class_methods($controller);
        $controllers = [];
        foreach ($methods as $method) {
            if (preg_match('/(\w+)Controller$/', $method, $parts)) {
                if (!$controller->isMethodHidden($method)) {
                    $controllers[] = $this->camelcaseToHyphenated($parts[1]);
                }
            }
        }
        return $controllers;
    }


    /**
     * Construct the method name for an endpoint
     * @param string $endpoint
     * @param string $method
     * @return string
     */
    protected function parseEndpointName($endpoint, $method = Request::METHOD_GET)
    {
        $endpoint = str_replace(' ', '', ucwords(str_replace(['-', '+', '%20'], ' ', $endpoint)));
        $method = strtolower($method);
        return $method . $endpoint . 'Endpoint';
    }

    /**
     * Construct the method name for a controller
     * @param string $controller
     * @return string
     */
    protected function parseControllerName($controller)
    {
        $controller = str_replace(' ', '', lcfirst(ucwords(str_replace(['-', '+', '%20'], ' ', $controller))));
        return $controller . 'Controller';
    }

    /**
     * Look at the request, fill out the parameters we have
     * @param Request $request
     * @param Controller $controller
     * @param $method
     * @return array
     */
    protected function getParametersFromRequest(Request $request, Controller $controller, $method)
    {
        $parameters = array();
        $reflectionMethod = new \ReflectionMethod($controller, $method);
        $reflectionParameters = $reflectionMethod->getParameters();
        foreach ($reflectionParameters as $reflectionParameter) {
            $value = $request->getParameter(
                $reflectionParameter->getName(),
                $reflectionParameter->isDefaultValueAvailable() ? $reflectionParameter->getDefaultValue() : null
            );
            if(
                $reflectionParameter->getClass() &&
                $reflectionParameter->getClass()->implementsInterface('\AyeAye\Formatter\Deserializable')
            ) {
                /** @var Deserializable $deserializable */
                $value = $reflectionParameter->getClass()
                                             ->newInstanceWithoutConstructor()
                                             ->ayeAyeDeserialize($value);
                $className = $reflectionParameter->getClass()->getName();
                if(!is_object($value) || !get_class($value) == $className) {
                    throw new \RuntimeException("$className::ayeAyeDeserialize did not return an instance of itself");
                }
            }
            $parameters[$reflectionParameter->getName()] = $value;
        }
        return $parameters;
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
