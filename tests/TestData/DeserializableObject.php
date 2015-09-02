<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 02/09/2015
 * Time: 10:40
 */

namespace AyeAye\Api\Tests\TestData;

use AyeAye\Formatter\Deserializable;

/**
 * Class DeserializableObject
 * @package AyeAye\Api\Tests\TestData
 */
class DeserializableObject implements Deserializable
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Take data and apply it to a fresh object
     * @param array $data
     * @return static
     */
    public static function ayeAyeDeserialize(array $data)
    {
        $object = new static();
        if(array_key_exists('data', $data)) {
            $object->data = $data['data'];
        }
        return $object;
    }

}