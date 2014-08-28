<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests\TestData;


use Gisleburt\Api\Controller;

class TestController extends Controller {

    protected $ignoreChildren = [
        'hiddenChild'
    ];

    protected  $children = [
        'me' => '\Gisleburt\Api\Tests\TestData\TestController',
        'child' => '\Gisleburt\Api\Tests\TestData\TestControllerChild',
        'hiddenChild' => '\stdClass',
    ];

    /**
     * Gets some information
     * @return string
     */
    public function getInformationAction() {
        return 'information';
    }

    /**
     * @param $condition string
     * @return \stdClass
     */
    public function getMoreInformationActon($condition) {
        $object = new \stdClass();
        $object->condition = $condition;
        return $object;
    }

    /**
     * @param $information string
     * @return bool
     */
    public function putInformationAction($information) {
        return true;
    }

} 