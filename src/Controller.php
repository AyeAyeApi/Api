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
    private $hiddenEndpoints = [
        'getIndexEndpoint' => true, // Value not used
    ];

    /**
     * Controllers that should not be publicly listed
     * @var string
     */
    private $hiddenControllers = [

    ];

    /**
     * The status object that represents an HTTP status
     * @var Status
     */
    private $status;

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

    /**
     * Set the status object associated with the controller using an HTTP status code
     * @param $statusCode
     * @return $this
     */
    protected function setStatusCode($statusCode)
    {
        $this->setStatus(new Status($statusCode));
        return $this;
    }

    /**
     * Hide an endpoint
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function hideEndpointMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        $this->hiddenEndpoints[$methodName] = true;
        return $this;
    }

    /**
     * Is an endpoint currently hidden
     * @param $methodName
     * @return bool
     * @throws Exception
     */
    public function isEndpointMethodHidden($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        return is_array($this->hiddenEndpoints)
            && isset($this->hiddenEndpoints[$methodName]);
    }

    /**
     * Show a hidden endpoint
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function showEndpointMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        if ($this->isEndpointMethodHidden($methodName)) {
            unset($this->hiddenEndpoints[$methodName]);
        }
        return $this;
    }

    /**
     * Hide a controller
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function hideControllerMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        $this->hiddenControllers[$methodName] = true;
        return $this;
    }

    /**
     * Is a controller currently hidden
     * @param $methodName
     * @return bool
     * @throws Exception
     */
    public function isControllerMethodHidden($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        return is_array($this->hiddenControllers)
            && isset($this->hiddenControllers[$methodName]);
    }

    /**
     * Show a hidden controller
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function showControllerMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        if ($this->isControllerMethodHidden($methodName)) {
            unset($this->hiddenControllers[$methodName]);
        }
        return $this;
    }
}
