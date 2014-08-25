<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Loft Digital, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Controller;

class ControllerTest extends TestCase {

    public function testParseActionName() {
        $controller = new Controller();
        $controller->getEndpoints();

    }

}
 