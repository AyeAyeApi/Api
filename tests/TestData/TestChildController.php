<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests\TestData;


use Gisleburt\Api\Controller;

class TestChildController extends Controller {

    public function postComplexDataAction($param1, $param2, $param3, $param4) {
        return (object)[
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3,
            'param4' => $param4,
        ];
    }

} 