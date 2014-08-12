<?php
/**
 * Formats data as json
 * @author Daniel Mason
 * @copyright Loft Digital, 2014
 */

namespace Gisleburt\Api\Formats;


use Gisleburt\Api\Format;

class Php extends Format {

    public function sendHeaders() {
        header('Content-Type: application/json');
    }

    public function format($data, $name = null) {
        return serialize($data);
    }

} 