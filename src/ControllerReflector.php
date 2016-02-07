<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07/02/2016
 * Time: 15:22
 */

namespace AyeAye\Api;

/**
 * Class ControllerReflector
 * @package AyeAye\Api
 */
class ControllerReflector
{
    /**
     * Reflects a given controller.
     * Exists purely for Dependency Injection
     * @param Controller $controller
     * @return ReflectionController
     */
    public function reflectController(Controller $controller)
    {
        return new ReflectionController($controller);
    }
}
