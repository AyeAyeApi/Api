<?php
/**
 * ControllerReflector.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

/**
 * Class ControllerReflector
 *
 * Takes a Controller and turns it into a ReflectionController.
 *
 * This class exists purely for dependency injection. Use ControllerReflectorInjector to replace it in order to
 * replace ReflectionController.
 *
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class ControllerReflector
{
    /**
     * Reflects a given controller.
     *
     * Exists purely for Dependency Injection.
     *
     * @param Controller $controller
     * @return ReflectionController
     */
    public function reflectController(Controller $controller)
    {
        return new ReflectionController($controller);
    }
}
