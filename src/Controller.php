<?php
/**
 * Controller.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\StatusInjector;

/**
 * Class Controller
 * Describes endpoints and controllers
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Controller
{

    use StatusInjector;

    /**
     * Endpoints that should not be publicly listed
     * @var string[]
     */
    private $hiddenMethods = [
        'getIndexEndpoint' => true, // Value not used
    ];

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
