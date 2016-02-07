<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07/02/2016
 * Time: 15:24
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\ControllerReflector;

/**
 * Trait ControllerReflectorInjector
 * @package AyeAye\Api\Injector
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
