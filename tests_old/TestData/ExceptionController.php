<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\TestsOld\TestData;

use AyeAye\Api\Controller;
use AyeAye\Api\Exception;

class ExceptionController extends Controller
{

    /**
     * For testing the generator aspect of Aye Aye
     * @throws \Exception
     */
    public function getExceptionEndpoint()
    {
        throw new \Exception();
    }

    /**
     * For testing the generator aspect of Aye Aye
     * @throws Exception
     */
    public function getAyeAyeExceptionEndpoint()
    {
        throw new Exception(418);
    }
}
