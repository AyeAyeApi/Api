<?php
/**
 * [Insert info here]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class TestBrokenController extends Controller
{


    protected $hiddenMethods;

    /**
     * Gets some information
     * @return string
     */
    public function getInformationEndpoint()
    {
        return 'information';
    }

    /**
     * A child controller
     * @return TestChildController
     */
    public function childController()
    {
        return new TestChildController();
    }
}
