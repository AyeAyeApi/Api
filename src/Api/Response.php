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
     * Response format. Defaults to json
     * @var string
     */
    protected $format = self::FORMAT_JSON;

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
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \Gisleburt\Api\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $format
     * @return bool
     */
    public function setFormat($format) {
        if(in_array($format, self::$acceptableFormats)) {
            $this->format = $format;
            return true;
        }
        return false;
    }

    /**
     * Used by PHP to get json object
     * @return array
     */
    public function jsonSerialize() {
        return [
            'status' => $this->getStatus(),
            'request' => $this->getRequest(),
            'data' => $this->getData(),
        ];
    }

    public function __toString() {
        $toStringMethod = 'to'.ucfirst($this->format);
        if(method_exists($this, $toStringMethod)) {
            return $this->$toStringMethod();
        }
        return json_encode($this);
    }

    public function toJson() {
        return json_encode($this);
    }

    public function toXml() {

    }

}