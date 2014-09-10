<?php
/**
 * Response object
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;


use Gisleburt\Formatter\FormatFactory;
use Gisleburt\Formatter\Format;

class Response implements \JsonSerializable
{

    /**
     * Used to name the data object that is returned to the user where applicable
     * @var string
     */
    protected $responseName = 'response';

    /**
     * Response format. Defaults to json
     * @var FormatFactory
     */
    protected $formatFactory;

    /**
     * The format object used to format this response
     * @var
     */
    protected $format;

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
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $status = new Status($statusCode);
        return $this->setStatus($status);
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
        $this->setFormat($this->request->getFormat());
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
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param FormatFactory $formatFactory
     * @return $this
     */
    public function setFormatFactory(FormatFactory $formatFactory)
    {
        $this->formatFactory = $formatFactory;
        return $this;
    }

    /**
     * Set the format using a file suffix
     * @param $suffix
     * @return $this
     * @throws \Exception
     */
    public function setFormat($suffix)
    {
        if (!$this->formatFactory instanceof FormatFactory) {
            throw new Exception("Format factory not set");
        }
        $this->format = $this->formatFactory->getFormatFor($suffix);
        return $this;
    }

    /**
     * @return Format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Format the data and send as a response. Only one response can be sent
     * @return $this
     */
    public function respond()
    {
        $format = $this->formatFactory->getFormatFor(
            $this->request->getFormat()
        );

        if ($this->status instanceof Status) {
            header($this->status->getHttpHeader());
        }

        header("Content-Type: {$format->getContentType()}");
        echo $format->getHeader();
        echo $format->format($this->jsonSerialize(), $this->responseName);
        echo $format->getFooter();
        return $this;
    }

    /**
     * Used by PHP to get json object
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'status' => $this->getStatus(),
            'request' => $this->getRequest(),
            'data' => $this->getData(),
        ];
    }

}