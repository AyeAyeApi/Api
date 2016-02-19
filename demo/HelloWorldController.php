<?php
// HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Yo ho ho
     * @param string $name Optional, defaults to 'Captain'
     * @return string
     */
    public function getAyeAyeEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }

    /**
     * lol...
     * @return $this
     */
    public function ayeController()
    {
        return $this;
    }
}
