<?php
/**
 * Response object
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

use AyeAye\Formatter\FormatFactory;
use AyeAye\Formatter\Formatter;

/**
 * Describes response to client
 * @package AyeAye\Api
 */
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
     * @var string
     */
    protected $format;

    /**
	 * The HTTP status of the response
     * @var Status
     */
    protected $status;

    /**
	 * The initial request. This will only be shown if debug is on
     * @var Request
     */
    protected $request;

    /**
	 * The data you wish to return in the response
     * @var mixed
     */
    protected $data;

    /**
	 * Get the Status object assigned to the response
     * @return \AyeAye\Api\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
	 * Set the Status object that will report the HTTP status to the client
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
	 * Set the Status object that will report the HTTP status to the client using only the HTTP status code
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $status = new Status($statusCode);
        return $this->setStatus($status);
    }

    /**
	 * Get the Request the client made
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
	 * Set the Request. This will only be returned in debug mode
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
	 * Get the Data that is being returned
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
	 * Set the data that is to be returned
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
	 * Set the format factory that will be used to choose a formatter
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
	 * Get the Formatter that will format the Response
     * @return Formatter
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
        // If in raw mode, only return data
        if(is_null($this->request->getParameter('debug'))) {
            return [
                'data' => $this->getData()
            ];
        }

        // Otherwise return all information
        return [
            'status' => $this->getStatus(),
            'request' => $this->getRequest(),
            'data' => $this->getData(),
        ];
    }

}