<?php
/**
 * Author: Daniel Mason
 * Package: Api
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;

class GeneratorController extends Controller
{

    /**
     * For testing the generator aspect of Aye Aye
     * @return \Generator
     */
    public function getGeneratorEndpoint()
    {
        yield 'data';
        yield 'string' => 'string';
        yield 'integer' => 42;
    }
}
