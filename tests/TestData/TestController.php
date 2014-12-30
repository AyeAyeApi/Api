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
        'hidden-child'
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

    /**
     * This controller
     * @return $this
     */
    public function meController()
    {
        return $this;
    }

    /**
     * A child controller
     * @return TestChildController
     */
    public function childController()
    {
        return new TestChildController();
    }

    /**
     * A hidden controller
     * @return \stdClass
     */
    public function hiddenChildController()
    {
        return new \stdClass();
    }

} 