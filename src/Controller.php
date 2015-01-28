<?php

/**
 * Directs traffic to the correct end points.
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

/**
 * Describes end points and controllers
 * @author Daniel Mason
 * @package AyeAye\Api
 */
class Controller
{

    /**
     * Endpoints that should not be publicly listed
     * @var string[]
     */
    protected $hiddenEndpoints = [
        'getIndexEndpoint' => true, // Value not used
    ];

    /**
     * Controllers that should not be publicly listed
     * @var string
     */
    protected $hiddenControllers = [

    ];

    /**
     * The status object that represents an HTTP status
     * @var Status
     */
    protected $status;

    /**
     * Look at a request and work out what to do next.
     * Call a controller, or an endpoint on this controller.
     * @param Request $request
     * @param array $requestChain
     * @return mixed
     * @throws Exception
     */
    public function processRequest(Request $request, array $requestChain = null)
    {

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

            $potentialEndpoint = $this->parseEndpointName($nextLink, $request->getMethod());
            if (method_exists($this, $potentialEndpoint)) {
                return call_user_func_array(
                    [$this, $potentialEndpoint],
                    $this->getParametersFromRequest($request, $potentialEndpoint)
                );
            }

            $message = "Could not find controller or endpoint matching '$nextLink'";
            throw new Exception($message, 404);
        }

        $potentialEndpoint = $this->parseEndpointName('index', $request->getMethod());
        if (method_exists($this, $potentialEndpoint)) {
            return $this->$potentialEndpoint();
        }

        return $this->getIndexEndpoint();

    }

    /**
     * Construct the method name for an endpoint
     * @param string $endpoint
     * @param string $method
     * @return string
     */
    public function parseEndpointName($endpoint, $method = Request::METHOD_GET)
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
    public function parseControllerName($controller)
    {
        $controller = str_replace(' ', '', lcfirst(ucwords(str_replace(['-', '+', '%20'], ' ', $controller))));
        return $controller . 'Controller';
    }

    /**
     * Returns a list of possible endpoints and controllers
     * @return \stdClass
     */
    public function getIndexEndpoint()
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
     * Hide an endpoint
     * @param $methodName
     * @return $this
     */
    protected function hideEndpointMethod($methodName)
    {
        if (!is_array($this->hiddenEndpoints)) {
            $this->hiddenEndpoints = [];
        }
        $this->hiddenEndpoints[$methodName] = true;
        return $this;
    }

    /**
     * Is an endpoint currently hidden
     * @param $methodName
     * @return bool
     */
    protected function isEndpointMethodHidden($methodName)
    {
        return is_array($this->hiddenEndpoints)
            && isset($this->hiddenEndpoints[$methodName]);
    }

    /**
     * Show a hidden endpoint
     * @param $methodName
     * @return $this
     */
    protected function showEndpointMethod($methodName)
    {
        if ($this->isEndpointMethodHidden($methodName)) {
            unset($this->hiddenEndpoints[$methodName]);
        }
        return $this;
    }

    /**
     * Hide a controller
     * @param $methodName
     * @return $this
     */
    protected function hideControllerMethod($methodName)
    {
        if (!is_array($this->hiddenControllers)) {
            $this->hiddenControllers = [];
        }
        $this->hiddenControllers[$methodName] = true;
        return $this;
    }

    /**
     * Is a controller currently hidden
     * @param $methodName
     * @return bool
     */
    protected function isControllerHiddenMethod($methodName)
    {
        return is_array($this->hiddenControllers)
            && isset($this->hiddenControllers[$methodName]);
    }

    /**
     * Show a hidden controller
     * @param $methodName
     * @return $this
     */
    protected function showControllerMethod($methodName)
    {
        if ($this->isControllerHiddenMethod($methodName)) {
            unset($this->hiddenControllers[$methodName]);
        }
        return $this;
    }

    /**
     * Returns a list of endpoints attached to this class
     * @return array
     */
    public function getEndpoints()
    {
        $endpoints = [];
        $parts = [];
        $methods = get_class_methods($this);
        foreach ($methods as $classMethod) {
            if (preg_match('/([a-z]+)([A-Z]\w+)Endpoint$/', $classMethod, $parts)) {
                if (!$this->isEndpointMethodHidden($classMethod)) {
                    $method = strtolower($parts[1]);
                    $endpoint = $this->camelcaseToHyphenated($parts[2]);
                    if (!array_key_exists($method, $endpoints)) {
                        $endpoints[$method] = array();
                    }
                    $endpoints[$method][$endpoint] = $this->getMethodDocumentation($classMethod);
                }
            }
        }
        return $endpoints;
    }

    /**
     * Returns a list of controllers attached to this class
     * @return array
     */
    public function getControllers()
    {
        $methods = get_class_methods($this);
        $controllers = [];
        foreach ($methods as $method) {
            if (preg_match('/(\w+)Controller$/', $method, $parts)) {
                if (!$this->isControllerHiddenMethod($method)) {
                    $controllers[] = $this->camelcaseToHyphenated($parts[1]);
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
    protected function camelcaseToHyphenated($camelcaseString)
    {
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
