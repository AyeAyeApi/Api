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
    private $hiddenMethods = [
        'getIndexEndpoint' => true, // Value not used
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
    protected function hideMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        $this->hiddenMethods[$methodName] = true;
        return $this;
    }

    /**
     * Is an endpoint currently hidden
     * @param $methodName
     * @return bool
     * @throws Exception
     */
    public function isMethodHidden($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        return isset($this->hiddenMethods[$methodName]);
    }

    /**
     * Show a hidden endpoint
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function showMethod($methodName)
    {
        if (!method_exists($this, $methodName)) {
            throw new Exception(500, "The method '$methodName' does not exist in ".get_called_class());
        }
        if ($this->isMethodHidden($methodName)) {
            unset($this->hiddenMethods[$methodName]);
        }
        return $this;
    }
}
