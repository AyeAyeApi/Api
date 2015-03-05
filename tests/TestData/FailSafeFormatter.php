<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 04/03/2015
 * Time: 10:08
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Formatter\Formatter;

class FailSafeFormatter extends Formatter
{
    public function format($data, $name = '') {
        throw new \Exception(FailSafeController::SYSTEM_MESSAGE);
    }
}