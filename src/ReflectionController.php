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
 *
 * Provides functionality similar to ReflectionObject (which is what is used underneath) to provide Controller specific
 * code reflection.
 *
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
     *
     * Initialise a ReflectionController with a valid controller. It will be
     * reflected into a reflection object that will allow us to analyse its
     * features.
     *
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->reflection = new \ReflectionObject($controller);
    }

    /**
     * Check for a particular endpoint.
     *
     * Takes the request method (http verb) and endpoint name and checks for a
     * matching class method.
     *
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
     * Call an endpoint and get the result.
     *
     * This method will parse the request into the endpoint methods parameters
     * so the writer of an endpoint need only describe the information they
     * need in the method declaration.
     *
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
     * Creates an array of parameters with which to call a method.
     *
     * Matches the parameters in a given method to the values available in the
     * given request. For absent values, either the default, or null, will be
     * used.
     *
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
     * Construct the method name for an endpoint.
     *
     * Endpoint methods are constructed like this [method][name]Endpoint. The
     * method name is lower cased, the endpoint name is upper camel cased, and
     * the class method name ends with the word "Endpoint".
     *
     * @example GET hello-world => getHelloWorldEndpoint
     *
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
     *
     * Takes the requested child controller name and checks for a corresponding
     * class method name. (Note: The class method will end with the word
     * Controller)
     *
     * @param $controllerName
     * @return bool
     */
    public function hasChildController($controllerName)
    {
        $methodName = $this->parseControllerName($controllerName);
        return $this->reflection->hasMethod($methodName);
    }

    /**
     * Get the child controller object.
     *
     * Gets the new child controller object from the child controller class
     * method. If the method does not exist, or the returned value is not a
     * controller object, a \RuntimeException is thrown.
     *
     * @throws \RuntimeException
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
        if (!is_object($controller) || !$controller instanceof Controller) {
            throw new \RuntimeException("{$this->reflection->getName()}::{$methodName} did not return a Controller");
        }
        return $controller;
    }

    /**
     * Construct the method name for a controller
     *
     * The methods for child controllers are simple named [name]Controller
     * where [name] is lower camel cased.
     *
     * @example hello-world => helloWorldController
     *
     * @param string $controller
     * @return string
     */
    protected function parseControllerName($controller)
    {
        $controller = str_replace(' ', '', lcfirst(ucwords(str_replace(['-', '+', '%20'], ' ', $controller))));
        return $controller . 'Controller';
    }

    /**
     * Get the status of the controller.
     *
     * This is a simple wrapper function.
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->controller->getStatus();
    }

    /**
     * Get the documentation for a controller.
     *
     * Creates a documentation object from the reflected controller.
     *
     * @return ControllerDocumentation
     */
    public function getDocumentation()
    {
        return new ControllerDocumentation($this->reflection);
    }
}
