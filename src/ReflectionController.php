<?php
/**
 * ReflectionController.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\Deserializable;

/**
 * Class ReflectionController
 * Provides functionality similar to ReflectionObject (which is what is used underneath) to provide Controller specific
 * code reflection.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class ReflectionController
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * ReflectionController constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->reflection = new \ReflectionObject($controller);
    }

    /**
     * Check if an endpoint exists for a particular method (http verb)
     * @param $method
     * @param $endpointName
     * @return bool
     */
    public function hasEndpoint($method, $endpointName)
    {
        $methodName = $this->parseEndpointName($method, $endpointName);
        return $this->reflection->hasMethod($methodName);
    }

    /**
     * @param $method
     * @param $endpointName
     * @param Request $request
     * @return mixed
     */
    public function getEndpointResult($method, $endpointName, Request $request)
    {
        $methodName = $this->parseEndpointName($method, $endpointName);
        if (!$this->reflection->hasMethod($methodName)) {
            throw new \RuntimeException("{$this->reflection->getName()}::{$methodName} does not exist");
        }

        $reflectionMethod = $this->reflection->getMethod($methodName);
        return $reflectionMethod->invokeArgs(
            $this->controller,
            $this->mapRequestToArguments($reflectionMethod, $request)
        );
    }

    /**
     * Creates an array of parameters with which to call a method
     * @param \ReflectionMethod $method
     * @param Request $request
     * @return array
     */
    protected function mapRequestToArguments(\ReflectionMethod $method, Request $request)
    {
        $map = [];
        foreach ($method->getParameters() as $parameter) {
            $value = $request->getParameter(
                $parameter->getName(),
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null
            );
            if ($parameter->getClass() &&
                $parameter->getClass()->implementsInterface(Deserializable::class)
            ) {
                /** @var Deserializable $deserializable */
                $value = $parameter->getClass()
                    ->newInstanceWithoutConstructor()
                    ->ayeAyeDeserialize($value);
                $className = $parameter->getClass()->getName();
                if (!is_object($value) || get_class($value) !== $className) {
                    throw new \RuntimeException("$className::ayeAyeDeserialize did not return an instance of itself");
                }
            }
            $map[$parameter->getName()] = $value;
        }
        return $map;
    }

    /**
     * Construct the method name for an endpoint
     * @param string $method
     * @param string $endpoint
     * @return string
     */
    protected function parseEndpointName($method, $endpoint)
    {
        $endpoint = str_replace(' ', '', ucwords(str_replace(['-', '+', '%20'], ' ', $endpoint)));
        $method = strtolower($method);
        return $method . $endpoint . 'Endpoint';
    }

    /**
     * Check if the named controller exists as a child of this controller.
     * Note: The name of this is the name of the controller NOT the name of the method, so exclude the word Controller
     * on the end.
     * @param $controllerName
     * @return bool
     */
    public function hasChildController($controllerName)
    {
        $methodName = $this->parseControllerName($controllerName);
        return $this->reflection->hasMethod($methodName);
    }

    /**
     * Get the child controller object
     * @throws \RuntimeException If the result of calling the child controller method was not a Controller object
     * @param $controllerName
     * @return Controller
     */
    public function getChildController($controllerName)
    {
        $methodName = $this->parseControllerName($controllerName);
        if (!$this->reflection->hasMethod($methodName)) {
            throw new \RuntimeException("{$this->reflection->getName()}::{$methodName} does not exist");
        }

        $controller = $this->reflection->getMethod($methodName)->invokeArgs($this->controller, []);
        if (!$controller instanceof Controller) {
            throw new \RuntimeException("{$this->reflection->getName()}::{$methodName} did not return a Controller");
        }
        return $controller;
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
     * @return Status
     */
    public function getStatus()
    {
        return $this->controller->getStatus();
    }

    /**
     * Returns a list of possible endpoints and controllers
     * @return ControllerDocumentation
     */
    public function getDocumentation()
    {
        return new ControllerDocumentation($this->reflection);
    }
}
