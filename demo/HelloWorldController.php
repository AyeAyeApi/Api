<?php
// HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getHelloEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }
}

