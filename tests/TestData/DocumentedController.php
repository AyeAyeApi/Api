<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class DocumentedController extends Controller
{

    /**
     * Test Summary
     * on two lines.
     * Test Description
     * on
     * three lines.
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
     *
     * This is a
     * three line summary
     * with a break
     *
     * This is a one line description
     * @return $this
     */
    public function selfReferenceController()
    {
        return $this;
    }

    /**
     * This is a summary. There is no description
     * @return null|mixed
     */
    public function getNullEndpoint()
    {
        return null;
    }

    public function noDocumentation()
    {
        return;
    }
}
