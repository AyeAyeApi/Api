<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;


use Gisleburt\Api\Controller;

class ControllerTest extends TestCase {

    public function testParseActionName() {
        $controller = new Controller();
        $controller->getEndpoints();

    }

}
 