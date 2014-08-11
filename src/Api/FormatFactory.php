<?php
/**
 * [Description]
 * @author Daniel Mason
 * @copyright Loft Digital, 2014
 */

namespace Gisleburt\Api;


class FormatFactory {

    protected $formats;

    public function __construct(array $formats) {
        $this->formats = $formats;
    }

    /**
     * @param $suffix
     * @return Format
     * @throws \Exception
     */
    public function getFormatFor($suffix) {
        if(array_key_exists($suffix, $this->formats)) {
            if($this->formats[$suffix] instanceof Format) {
                return $this->formats[$suffix];
            }
            if(is_string($this->formats[$suffix]) && class_exists($this->formats[$suffix])) {
                $format = new $this->formats[$suffix]();
                if($format instanceof Format) {
                    return $format;
                }
            }
            throw new \Exception("Format for '$suffix' not a class or Format object");
        }
        throw new \Exception("Format for '$suffix' not found");
    }

}