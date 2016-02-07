<?php

namespace AyeAye\Api\Injector;

use AyeAye\Api\Router;

trait RouterInjector
{

    /**
     * The router that will direct the request
     * @var Router
     */
    private $router;

    /**
     * Set the router to be used when go is called
     * @param Router $router
     * @return $this;
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get the router
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
