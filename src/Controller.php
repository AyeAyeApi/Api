<?php
/**
 * Controller.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\StatusInjector;

/**
 * Class Controller
 * Describes endpoints and child controllers
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Controller
{
    use StatusInjector;

    /**
     * Methods (controllers and endpoints) that should not be publicly listed.
     * @var string[]
     */
    private $hiddenMethods = [
        'getIndexEndpoint' => true, // Value not used
    ];

    /**
     * Hide a method
     *
     * When a controller or endpoint method is hidden, it will no longer be
     * automatically listed in the controllers index. It will, however, still
     * be accessible.
     *
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
     * Is a method currently hidden.
     *
     * This is used to determine if it should be indexed or not.
     *
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
     * Show a hidden method.
     *
     * This reveals what would otherwise be a hidden method, allowing it to be
     * indexed. If the named method does not exist, it will throw an exception
     * via isMethodHidden.
     *
     * If the method exists but is not hidden this will not do anything.
     *
     * @param $methodName
     * @return $this
     * @throws Exception
     */
    protected function showMethod($methodName)
    {
        if ($this->isMethodHidden($methodName)) {
            unset($this->hiddenMethods[$methodName]);
        }
        return $this;
    }
}
