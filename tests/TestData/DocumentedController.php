<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class DocumentedController extends Controller {

    /**
     * Test Summary.
     * Test Description.
     * @param        $incomplete
     * @param int    $integer    Test integer
     * @param string $string     Test string
     * Second line
     * @return string
     */
    public function getDocumentedEndpoint($incomplete, $integer, $string)
    {
        return "information";
    }

    /**
     * Recursive self reference controller
     * @return $this
     */
    public function selfReferenceController()
    {
        return $this;
    }

}