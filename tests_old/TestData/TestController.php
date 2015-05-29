<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\TestsOld\TestData;

use AyeAye\Api\Controller;

class TestController extends Controller
{

    public function __construct()
    {
        $this->hideMethod('hiddenChildController');
        $this->hideMethod('getHiddenEndpoint');
    }

    /**
     * Gets some information
     * @return string
     */
    public function getInformationEndpoint()
    {
        return 'information';
    }

    /**
     * A post index. Why? Who knows.
     * @return string
     */
    public function postIndexEndpoint()
    {
        return 'Why are you posting to the index?';
    }

    /**
     * Get some conditional information
     * @param string $condition The condition for the information
     * @return \stdClass
     */
    public function getMoreInformationEndpoint($condition)
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
    public function putInformationEndpoint($information)
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

    public function getHiddenEndpoint()
    {
        return 'You found me!';
    }

    /**
     * This is the endpoint where PHPDoc is indented randomly
     *
     * @param string $first Some string
     * @param    float     $second     Parameter with different indentation
     *
     * @return bool Always true
     */
    public function getRandomIndentationEndpoint($first, $second)
    {
        return true;
    }
}
