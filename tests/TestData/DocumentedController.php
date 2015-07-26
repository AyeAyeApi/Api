<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class DocumentedController extends Controller {

    /**
     * Test Summary
     * Test Description.
     * @param $incomplete
     * @param int    $int    Test integer
     * @param string $string Test string
     * @return string
     */
    public function getDocumentedEndpoint($incomplete, $int, $string)
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