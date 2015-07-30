<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class IndexedController extends Controller {


    /**
     * The GET index
     * @return string
     */
    public function getIndexEndpoint()
    {
        return 'Got Index';
    }

    /**
     * The PUT index
     * @return string
     */
    public function putIndexEndpoint()
    {
        return 'Put Index';
    }

    /**
     * @return string
     */
    public function getHelloWorldEndpoint()
    {
        return 'Hello World';
    }

    /**
     * @return string
     */
    public function putHelloWorldEndpoint()
    {
        return 'Hello to you too';
    }

}