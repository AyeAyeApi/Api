<?php
/**
 * Response object
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Api;


class Response {

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_HTML = 'html';

    public static $acceptableFormats = array(
        self::FORMAT_JSON,
        self::FORMAT_XML,
        self::FORMAT_HTML,
    );

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var
     */
    protected $request;

    /**
     * @var mixed The data you wish to return in the response
     */
    protected $data;

} 