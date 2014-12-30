<?php

/**
 * Directs traffic to the correct end points.
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

/**
 * Describes end points and child controllers
 * @author Daniel Mason
 * @package AyeAye\Api
 */
class Controller
{

    /**
     * Endpoints that should not be publicly listed
     * @var string[]
     */
    protected $ignoreEndpoints = [
        'index',
    ];

    /**
     * Children that should not be publicly listed
     * @var string
     */
    protected $ignoreChildren = [

    ];

    /**
     * The request object that represents the users request
     * @var Request
     */
    protected $request;

    /**
	 * The status object that represents an HTTP status
     * @var Status
     */
    protected $status;

    /**
	 * Look at a request and work out what to do next.
	 * Call a child controller, or an endpoint on this controller.
     * @param Request $request
     * @param array $requestChain
     * @return mixed
     * @throws Exception
     */
    public function processRequest(Request $request, array $requestChain = null)
    {

        // Set these internally in case we require access to them
        $this->request = $request;

        if (is_null($requestChain)) {
            $requestChain = $request->getRequestChain();
        }

        $nextLink = array_shift($requestChain);
        if ($nextLink) {
            $potentialController = $this->parseControllerName($nextLink);
            if (method_exists($this, $potentialController)) {
                /** @var Controller $controller */
                $controller = $this->$potentialController();
                $data = $controller->processRequest($request, $requestChain);
                $this->status = $controller->getStatus();
                return $data;
            }

            $potentialAction = $this->parseActionName($nextLink, $this->request->getMethod());
            if (method_exists($this, $potentialAction)) {
                return call_user_func_array(
                    [$this, $potentialAction],
                    $this->getParametersFromRequest($request, $potentialAction)
                );
            }

            $message = "Could not find controller or action matching '$nextLink'";
            throw new Exception($message, 404);
        }

        $potentialAction = $this->parseActionName('index', $this->request->getMethod());
        if (method_exists($this, $potentialAction)) {
            return $this->$potentialAction();
        }

        return $this->getIndexAction();

    }

    /**
     * Construct the method name for an action
     * @param string $action
     * @param string $method
     * @return string
     */
    public function parseActionName($action, $method = Request::METHOD_GET)
    {
        $action = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        $method = strtolower($method);
        return $method . $action . 'Action';
    }

    /**
     * Construct the method name for a controller
     * @param string $controller
     * @return string
     */
    public function parseControllerName($controller) {
        $controller = str_replace(' ', '', lcfirst(ucwords(str_replace('-', ' ', $controller))));
        return $controller . 'Controller';
    }

    /**
     * Returns a list of possible actions and child controllers
     * @return \stdClass
     */
    public function getIndexAction()
    {
        $data = new \stdClass();
        $data->controllers = $this->getControllers();
        $data->endpoints = $this->getEndpoints();
        return $data;
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
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
	 * Set the status object associated with the controller using an HTTP status code
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->setStatus(new Status($statusCode));
        return $this;
    }

    /**
     * Returns a list of actions attached to this class
     * @return array
     */
    public function getEndpoints()
    {
        $endPoints = [];
        $parts = [];
        $methods = get_class_methods($this);
        foreach ($methods as $classMethod) {
            if (preg_match('/([a-z]+)([A-Z]\w+)Action$/', $classMethod, $parts)) {
                $method = strtolower($parts[1]);
                $endPoint = $this->camelcaseToHyphenated($parts[2]);
                if (!in_array($endPoint, $this->ignoreEndpoints)) {
                    if (!array_key_exists($method, $endPoints)) {
                        $endPoints[$method] = array();
                    }
                    $endPoints[$method][$endPoint] = $this->getMethodDocumentation($classMethod);
                }
            }
        }
        return $endPoints;
    }

    /**
     * Returns a list of controllers attached to this class
     * @return array
     */
    public function getControllers() {
        $methods = get_class_methods($this);
        $controllers = [];
        foreach ($methods as $method) {
            if (preg_match('/(\w+)Controller$/', $method, $parts)) {
                $controller = $this->camelcaseToHyphenated($parts[1]);
                if (!in_array($controller, $this->ignoreChildren)) {
                    $controllers[] = $controller;
                }
            }
        }
        return $controllers;
    }

    /**
     * Takes a camelcase string, such as method names, and hyphenates it for urls
     * @param string $camelcaseString
     * @return string Hyphenated string for urls
     */
    protected function camelcaseToHyphenated($camelcaseString) {
        return strtolower(preg_replace('/([a-z])([A-Z])/s', '$1-$2', $camelcaseString));
    }

    /**
     * Looks at the PHPDoc for the given method and returns an array of information it
     * @param $method
     * @return array
     */
    public function getMethodDocumentation($method)
    {
        $reflectionMethod = new \ReflectionMethod($this, $method);
        $doc = $reflectionMethod->getDocComment();

        // Description
        $description = '';
        preg_match_all('/\*\s+(\w[^@^\n^\r]+)/', $doc, $results);
        if (array_key_exists(1, $results)) {
            $description = implode(' ', $results[1]);
        }

        // Parameters
        $parameters = array();
        $nMatches = preg_match_all('/@param (\S+) \$?(\S+) ?([\S ]+)?/', $doc, $results);
        for ($i = 0; $i < $nMatches; $i++) {
            $parameter = new \stdClass();
            $parameter->type = $results[1][$i];
            if ($results[3][$i]) {
                $parameter->description = $results[3][$i];
            }
            $parameters[$results[2][$i]] = $parameter;
        }

        return [
            'description' => $description,
            'parameters' => $parameters,
        ];
    }

    /**
     * Look at the request, fill out the parameters we have
     * @param Request $request
     * @param $method
     * @return array
     */
    protected function getParametersFromRequest(Request $request, $method)
    {
        $parameters = array();
        $reflectionMethod = new \ReflectionMethod($this, $method);
        $reflectionParameters = $reflectionMethod->getParameters();
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = $request->getParameter(
                $reflectionParameter->getName(),
                $reflectionParameter->isDefaultValueAvailable() ? $reflectionParameter->getDefaultValue() : null
            );
        }
        return $parameters;
    }

}