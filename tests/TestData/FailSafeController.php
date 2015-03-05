<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 03/03/2015
 * Time: 20:27
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Api\Controller;
use AyeAye\Api\Exception;

class FailSafeController extends Controller
{

    const PUBLIC_MESSAGE = 'This is in the response';
    const SYSTEM_MESSAGE = 'This is not in the response';

    public function getAyeAyeExceptionEndpoint()
    {
        throw new Exception(static::PUBLIC_MESSAGE, 500, static::SYSTEM_MESSAGE);
    }

    public function getBasicExceptionEndpoint()
    {
        throw new \Exception(static::SYSTEM_MESSAGE);
    }
}
