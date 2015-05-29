<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;


class TestGeneratorController {

    public function getGeneratorEndpoint()
    {
        yield 'Normal Data';
        yield 'extra' => 'Further Information';
    }

}