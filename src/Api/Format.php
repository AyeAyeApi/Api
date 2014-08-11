<?php
/**
 * Format data before sending it to client
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;


abstract class Format {

    /**
     * Send document headers
     */
    public function sendHeaders() {
        return;
    }

    /**
     * Format the data
     * @param $data
     * @return string
     */
    abstract public function format($data);

    /**
     * Get anything that must come before any data
     * @return string
     */
    public function getHeader() {
        return '';
    }

    /**
     * Get anything that must come after data
     * @return string
     */
    public function getFooter() {
        return '';
    }

} 