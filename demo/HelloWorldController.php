<?php
// HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Yo ho ho
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getAyeAyeEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }

    /**
     * lol...
     * @returns $this
     */
    public function ayeController()
    {
        return $this;
    }
}
