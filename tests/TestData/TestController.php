<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests\TestData;


use AyeAye\Api\Controller;

class TestController extends Controller
{

    protected $ignoreChildren = [
        'hiddenChild'
    ];

    protected $children = [
        'me' => '\AyeAye\Api\Tests\TestData\TestController',
        'child' => '\AyeAye\Api\Tests\TestData\TestChildController',
        'hiddenChild' => '\stdClass',
    ];

    /**
     * Gets some information
     * @return string
     */
    public function getInformationAction()
    {
        return 'information';
    }

    /**
     * Get some conditional information
     * @param string $condition The condition for the information
     * @return \stdClass
     */
    public function getMoreInformationAction($condition)
    {
        $object = new \stdClass();
        $object->condition = $condition;
        return $object;
    }

    /**
     * Put some information into the system
     * @param $information string The information to put
     * @return bool
     */
    public function putInformationAction($information)
    {
        return true;
    }

} 