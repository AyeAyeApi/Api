<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 02/09/2015
 * Time: 10:45
 */

namespace AyeAye\Api\TestsOld\TestData;


use AyeAye\Api\Controller;

class DeserializeController extends Controller
{

    public function getDeserializeEndpoint(DeserializableObject $object)
    {
        return $object;
    }

}