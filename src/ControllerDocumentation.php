<?php
/**
 * ControllerDocumentation.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\Serializable;

/**
 * Class ControllerDocumentation
 * Parses the relevant child controller and endpoint methods of a Controller object through a Documentation object and
 * returns the result as an array. This is used to provide API documentation to end users.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class ControllerDocumentation implements Serializable
{
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
     * @param \ReflectionObject $reflectedController
     */
    public function __construct(\ReflectionObject $reflectedController)
    {
        if (!$reflectedController->isSubclassOf(Controller::class)
            && $reflectedController->getName() !== Controller::class
        ) {
            throw new \InvalidArgumentException(
                'The ControllerDocumentation class can only document Controllers'
            );
        }
        $this->reflectedController = $reflectedController;
        $this->documentation = new Documentation();
    }

    /**
     * Returns a list of endpoints attached to the controller being examined
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
     * Returns a list of controllers attached to the controller being examined
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
     * Takes a camelcase string, such as method names, and hyphenates it for urls
     * @param string $camelcaseString
     * @return string Hyphenated string for urls
     */
    protected function camelcaseToHyphenated($camelcaseString)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/s', '$1-$2', $camelcaseString));
    }

    /**
     * Gets a callable method from the original controller
     * @param string $methodName
     * @return \Closure
     */
    protected function getControllerMethod($methodName)
    {
        $reflectionMethod = $this->reflectedController->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        return function () use ($reflectionMethod) {
            return $reflectionMethod->invokeArgs($this->reflectedController, func_get_args());
        };
    }

    /**
     * Returns the documentation of the controller
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
     * Returns the documentation of the controller
     * @return array
     */
    public function ayeAyeSerialize()
    {
        return $this->getDocumentation();
    }
}
