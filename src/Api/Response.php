<?php
/**
 * Response object
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;


class Response implements \JsonSerializable {

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_HTML = 'html';

    public static $acceptableFormats = array(
        self::FORMAT_JSON,
        self::FORMAT_XML,
        self::FORMAT_HTML,
    );

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var mixed The data you wish to return in the response
     */
    protected $data;

    /**
     * @return \Gisleburt\Api\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Gisleburt\Api\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function jsonSerialize() {
        return [
            'status' => $this->getStatus(),
            'request' => $this->getRequest(),
            'data' => $this->getData(),
        ];
    }

} 