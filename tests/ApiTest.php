<?php
/**
 * Tests the controller class
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api\Tests;

use Gisleburt\Api\Api;
use Gisleburt\Api\Controller;
use Gisleburt\Api\Request;
use Gisleburt\Api\Status;
use Gisleburt\Api\Tests\TestData\TestController;

class ApiTest extends TestCase
{

    /**
     * @runInSeparateProcess
     */
    public function testQuickStart()
    {
        $initialController = new TestController();
        $api = new Api($initialController);

        ob_start();

        $api->go()->respond();

        $output = json_decode(ob_get_contents());

        ob_clean();


        // Children

        $this->assertTrue(
            in_array('me', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            in_array('child', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertFalse(
            in_array('hiddenChild', $output->data->children),
            "Children should have contained 'me'"
        );

        $this->assertTrue(
            count($output->data->children) == 2,
            "Children should have has 2 elements, it had: " . PHP_EOL . count($output->data->children)
        );

        // Endpoints


        $this->assertTrue(
            property_exists($output->data->endpoints->get, 'information'),
            "Get endpoints should have included 'information' it didn't"
        );

        $this->assertTrue(
            $output->data->endpoints->get->information->description === 'Gets some information',
            "Get Information description was wrong"
        );

        $this->assertTrue(
            count($output->data->endpoints->get->information->parameters) === 0,
            "Get Information description should not contain any parameters"
        );

        $this->assertTrue(
            property_exists($output->data->endpoints->get, 'more-information'),
            "Get endpoints should have included more-information it didn't"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->description === 'Get some conditional information',
            "Get More Information description was wrong"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->parameters->condition->type === 'string',
            "Get More Information should take a string called condition"
        );

        $this->assertTrue(
            $output->data->endpoints->get->{'more-information'}->parameters->condition->description === 'The condition for the information',
            "Get More Information parameter should be described as 'The condition for the information'"
        );

        $this->assertTrue(
            count($output->data->endpoints->put) === 1,
            "There should have been 1 get endpoints, there were: " . PHP_EOL . count($output->data->endpoints->put)
        );

        $this->assertTrue(
            array_key_exists('information', $output->data->endpoints->put),
            "Put endpoints should have included 'information' it didn't"
        );

    }

}
 
