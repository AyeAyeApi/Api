<?php
/**
 * ControllerDocumentation.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\Serializable;

/**
 * Class ControllerDocumentation
 *
 * Parses the relevant child controller and endpoint methods of a Controller
 * object through a Documentation object and returns the result as an array.
 * This is used to provide API documentation to end users.
 *
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class ControllerDocumentation implements Serializable
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var \ReflectionObject
     */
    protected $reflectedController;

    /**
     * @var Documentation
     */
    protected $documentation;

    /**
     * Multi dimensional array of endpoint information
     * @var array[]
     */
    protected $endpointsCache;

    /**
     * A list of child controllers
     * @var string[]
     */
    protected $controllersCache;

    /**
     * ControllerDocumentation constructor.
     *
     * The reflection object must contain a valid controller class.
     *
     * @param Controller $controller
     * @param \ReflectionObject $reflectionController
     * @throws \InvalidArgumentException
     */
    public function __construct(Controller $controller, \ReflectionObject $reflectionController = null)
    {
        $this->controller = $controller;
        $this->reflectedController = $reflectionController;
        if(!$this->reflectedController) {
            $this->reflectedController = new \ReflectionObject($this->controller);
        }
        if(!$this->reflectedController->isInstance($controller)) {
            throw new \InvalidArgumentException(
                'Controller did not match ReflectionObject'
            );
        }
        $this->documentation = new Documentation();
    }

    /**
     * List the endpoints the controller provides.
     *
     * It is the endpoint name, not the method name that is given. Endpoints
     * are grouped by http verb and will have their documentation listed. If
     * the endpoint has been "hidden" by the controller, it will not be listed.
     *
     * @return array
     */
    protected function getEndpoints()
    {
        if (!$this->endpointsCache) {
            $isHidden = $this->getControllerMethod('isMethodHidden');
            $endpoints = [];
            $parts = [];
            $methods = $this->reflectedController->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (preg_match('/([a-z]+)([A-Z]\w+)Endpoint$/', $method->getName(), $parts)) {
                    if (!$isHidden($method->getName())) {
                        $httpVerb = strtolower($parts[1]);
                        $endpoint = $this->camelcaseToHyphenated($parts[2]);
                        if (!array_key_exists($httpVerb, $endpoints)) {
                            $endpoints[$httpVerb] = array();
                        }
                        $endpoints[$httpVerb][$endpoint] = $this->documentation->getMethodDocumentation($method);
                    }
                }
            }
            $this->endpointsCache = $endpoints;
        }
        return $this->endpointsCache;
    }

    /**
     * Lists child controllers the controller provides.
     *
     * It is the controller name, not the method name that is given. If the
     * controller has been "hidden" by the controller, it will not be listed.
     *
     * @return array
     */
    protected function getControllers()
    {
        if (!$this->controllersCache) {
            $isHidden = $this->getControllerMethod('isMethodHidden');
            $methods = $this->reflectedController->getMethods(\ReflectionMethod::IS_PUBLIC);
            $controllers = [];
            foreach ($methods as $method) {
                if (preg_match('/(\w+)Controller$/', $method->getName(), $parts)) {
                    if (!$isHidden($method->getName())) {
                        $controllers[] = $this->camelcaseToHyphenated($parts[1]);
                    }
                }
            }
            $this->controllersCache = $controllers;
        }
        return $this->controllersCache;
    }

    /**
     * Turns camel case strings into lowercase hyphenated strings.
     *
     * Hyphenated strings are more useful for breaking up words in urls.
     *
     * @param string $camelcaseString
     * @return string Hyphenated string for urls
     */
    protected function camelcaseToHyphenated($camelcaseString)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/s', '$1-$2', $camelcaseString));
    }

    /**
     * Gets a callable method from the original controller.
     *
     * Given a method name in the controller, this method will return a
     * callable function that will invoke the method with any arguments you
     * provide.
     *
     * @param string $methodName
     * @return \Closure
     */
    protected function getControllerMethod($methodName)
    {
        $reflectionMethod = $this->reflectedController->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        return function () use ($reflectionMethod) {
            return $reflectionMethod->invokeArgs($this->controller, func_get_args());
        };
    }

    /**
     * Returns the documentation of the controller.
     *
     * Returns an array containing bother child controllers and endpoints with
     * their respective documentation. This can be given to an end user so that
     * they can understand how this controller deals with requests.
     *
     * @return array
     */
    public function getDocumentation()
    {
        return [
            'controllers' => $this->getControllers(),
            'endpoints' => $this->getEndpoints(),
        ];
    }

    /**
     * Returns the documentation of the controller.
     *
     * @see ::getDocumentation
     * @return array
     */
    public function ayeAyeSerialize()
    {
        return $this->getDocumentation();
    }
}
