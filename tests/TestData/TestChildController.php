<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests\TestData;


use AyeAye\Api\Controller;

class TestChildController extends Controller
{

    public function postComplexDataEndpoint($param1, $param2, $param3, $param4)
    {
        return (object)[
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3,
            'param4' => $param4,
        ];
    }

} 