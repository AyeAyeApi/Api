<?php
/**
 * Formats data as xml
 * @author Daniel Mason
 * @copyright Loft Digital, 2014
 */

namespace Gisleburt\Api\Formats;


use Gisleburt\Api\Format;

class Xml extends Format {

    public function sendHeaders() {

    }

    public function getHeader() {
        return '<xml >';
    }

    public function format($data) {

    }

} 