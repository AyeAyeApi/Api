<?php
/**
 * RouterInjector.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\Router;

/**
 * Trait RouterInjector
 * Allows the injection and management of a Router object. Provides a default if one isn't set.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait RouterInjector
{
    /**
     * The router that will direct the request
     * @var Router
     */
    private $router;

    /**
     * Set the router to be used when go is called.
     *
     * Use this for dependency injection.
     *
     * @param Router $router
     * @return $this;
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get the router.
     *
     * If no router has been set, the default will be used.
     *
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->router) {
            $this->router = new Router();
        }
        return $this->router;
    }
}
