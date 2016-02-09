<?php
/**
 * ControllerReflectorInjector.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\ControllerReflector;

/**
 * Trait ControllerReflectorInjector
 * Allows the injection and management of a ControllerReflector object. Provides a default if one isn't set.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait ControllerReflectorInjector
{
    /**
     * @var ControllerReflector
     */
    private $controllerReflector;

    /**
     * @return ControllerReflector
     */
    public function getControllerReflector()
    {
        if (!$this->controllerReflector) {
            $this->controllerReflector = new ControllerReflector();
        }
        return $this->controllerReflector;
    }

    /**
     * @param ControllerReflector $controllerReflector
     * @return $this
     */
    public function setControllerReflector($controllerReflector)
    {
        $this->controllerReflector = $controllerReflector;
        return $this;
    }
}
