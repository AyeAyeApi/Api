<?php
/**
 * Formats data as xml
 * @author Daniel Mason
 * @copyright Loft Digital, 2014
 */

namespace Gisleburt\Api\Formats;


use Gisleburt\Api\Format;

class Xml extends Format {

    const DEFAULT_NODE_NAME = 'data';

    protected $numericArrayPrefix = '_';

    public function sendHeaders() {
        header('Content-Type: application/xml');
    }

    public function getHeader() {
        return '<?xml version="1.0" encoding="UTF-8" ?>';
    }

    public function format($data, $nodeName = null) {

        if(!$nodeName) {
            if(is_object($data)) {
                $nodeName = preg_replace('/.*\\\/', '', get_class($data));
                $nodeName = preg_replace('/\W/', '', $nodeName);
            }
            elseif(is_array($data)) {
                $nodeName = 'array';
            }
            else {
                $nodeName = static::DEFAULT_NODE_NAME;
            }
        }

        $xml = "<$nodeName>";
        foreach($data as $property => $value) {
            // Clear non-alphanumeric characters
            $property = preg_replace('/\W/', '', $property);

            // If numeric we'll stick a character in front of it, a bit hack but should be valid
            if(is_numeric($property)) {
                $property = $this->numericArrayPrefix.$property;
            }

            if(!is_scalar($value)) {
                if($value instanceof \JsonSerializable) {
                    $xml .= $this->format($value->jsonSerialize(), $property);
                }
                else {
                    $xml .= $this->format($value, $property);
                }
            }
            else {
                $xml .= "<{$property}>".htmlspecialchars($value)."</{$property}>";
            }
        }
        $xml .= "</$nodeName>";

        return $xml;
    }

} 