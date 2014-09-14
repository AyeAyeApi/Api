<?php

/**
 * Directs traffic to the correct end points.
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

use AyeAye\Api\Exception as ApiException;

class Controller
{

    /**
     * Controllers that this API links to
     * @var Controller[]
     */
    protected $children = [];

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
     * The request object
     * @var Request
     */
    protected $request;

    /**
     * @var Status
     */
    protected $status;

    /**
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
            if (array_key_exists($nextLink, $this->children)) {
                /** @var Controller $child */
                $child = new $this->children[$nextLink]();
                $data = $child->processRequest($request, $requestChain);
                $this->status = $child->getStatus();
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
            throw new Exception($message, 404, $message);
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
     * Returns a list of possible actions and child controllers
     * @return \stdClass
     */
    public function getIndexAction()
    {
        $data = new \stdClass();
        $data->children = $this->getChildren();
        $data->endpoints = $this->getEndpoints();
        return $data;
    }

    /**
     * Get a list of child controllers to this one
     * @return array
     */
    public function getChildren()
    {
        $children = [];
        foreach ($this->children as $child => $class) {
            if (!in_array($child, $this->ignoreChildren)) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
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
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
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
                $endPoint = strtolower(preg_replace('/([a-z])([A-Z])/s', '$1-$2', $parts[2]));
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
                null
            );
        }
        return $parameters;
    }

}