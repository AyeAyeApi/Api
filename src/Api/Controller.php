<?php

/**
 * Directs traffic to the correct end points.
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;

use Gisleburt\Api\Exception as ApiException;

class Controller {

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
     * @param Request $request
     * @param array $requestChain
     * @return mixed
     * @throws Exception
     */
    public function processRequest(Request $request, array $requestChain = []) {

        // Set these internally in case we require access to them
        $this->request = $request;

        $leftInChain = count($requestChain);

        // If there's more than one link left in the chain we need to go to the next child
        if($leftInChain > 1) {
            $nextLink = array_shift($requestChain);
            if(array_key_exists($nextLink, $this->children)) {
                /** @var Controller $child */
                $child = new $this->children[$nextLink];
                return $child->processRequest($request, $requestChain);
            }
            throw new ApiException("Could not find controller $nextLink", 404);
        }
        // If there's exactly one link left in the chain we should try to call the named action
        elseif($leftInChain == 1) {
            $finalLink = array_shift($requestChain);
            $potentialAction = $this->parseActionName($finalLink, $this->request->getMethod());
            if(method_exists($this, $potentialAction)) {
                return $this->$potentialAction();
            }
            throw new ApiException("Could not find action $finalLink", 404);
        }
        // If we ran out of links, but we're in a controller, we'll try to get the index action
        else {
            $potentialAction = $this->parseActionName('index', $this->request->getMethod());
            if(method_exists($this, $potentialAction)) {
                return $this->$potentialAction();
            }
            $potentialAction = $this->parseActionName('index', Request::METHOD_GET);
            if(method_exists($this, $potentialAction)) {
                return $this->$potentialAction();
            }
            throw new ApiException('Could not find an appropriate action', 404);
        }

    }

    /**
     * Construct the method name for an action
     * @param string $action
     * @param string $method
     * @return string
     */
    protected function parseActionName($action, $method = Request::METHOD_GET) {
        $action = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        $method = strtolower($method);
        return $method.$action.'Action';
    }

    /**
     * Returns a list of possible actions and child controllers
     * @return \stdClass
     */
    public function getIndexAction() {
        $response = new \stdClass();
        $response->children = $this->getChildren();
        $response->endpoints = $this->getEndpoints();
        return $response;
    }

    /**
     * Get a list of child controllers to this one
     * @return array
     */
    public function getChildren() {
        $children = [];
        foreach($this->children as $child) {
            if(!in_array($child, $this->ignoreChildren)) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
     * Returns a list of actions attached to this class
     * @return array
     */
    public function getEndpoints() {
        $endPoints = [];
        $parts = [];
        $methods = get_class_methods($this);
        foreach($methods as $classMethod) {
            if(preg_match('/([a-z]+)([A-Z]\w+)Action$/', $classMethod, $parts)) {
                $method = strtolower($parts[1]);
                $endPoint = strtolower(preg_replace('/([a-z])([A-Z])/s','$1-$2', $parts[2]));
                if(!in_array($endPoint, $this->ignoreEndpoints)) {
                    if(!array_key_exists($method, $endPoints))
                        $endPoints[$method] = array();
                    $endPoints[$method][$endPoint] = $this->getParametersFromDocumentation($classMethod);
                }
            }
        }
        return $endPoints;
    }

    /**
     * Looks at the PHPDoc for the given method and returns an array of information about the parameters it takes
     * @param $method
     * @return array
     */
    public function getParametersFromDocumentation($method) {
        $parameters = array();
        $reflectionMethod = new \ReflectionMethod($this, $method);
        $doc = $reflectionMethod->getDocComment();
        $nMatches = preg_match_all('/@param (\S+) \$?(\S+) ?([\S ]+)?/', $doc, $results);
        for($i = 0; $i < $nMatches; $i++) {
            $parameter = new \stdClass();
            $parameter->parameter = $results[2][$i];
            $parameter->type = $results[1][$i];
            if($results[3][$i])
                $parameter->description = $results[3][$i];
            $parameters[] = $parameter;
        }
        return $parameters;
    }

    /**
     * Look at the request, fill out the parameters we have
     * @param Request $request
     * @param $method
     * @return array
     */
    public function responseToParameters(Request $request, $method) {
        $parameters = array();
        $reflectionMethod = new \ReflectionMethod($this, $method);
        $reflectionParameters = $reflectionMethod->getParameters();
        foreach($reflectionParameters as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = $request->getParameter($reflectionParameter->getName(), null);
        }
        return $parameters;
    }

}