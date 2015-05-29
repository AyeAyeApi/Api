<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\TestsOld\TestData;


class TestGeneratorController {

    public function getGeneratorEndpoint()
    {
        yield 'Normal Data';
        yield 'extra' => 'Further Information';
    }

}