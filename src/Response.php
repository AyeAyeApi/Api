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
     * The formatter object used to format this response
     * @var Formatter
     */
    protected $formatter;

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
    protected $body = [];

    /**
     * @var string
     */
    protected $preparedResponse;

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
        return $this;
    }

    /**
     * Get the specifically requested data that is being returned
     * @return mixed
     */
    public function getData()
    {
        if (array_key_exists('data', $this->body)) {
            return $this->body['data'];
        }
        return null;
    }

    /**
     * Get all data that is being returned
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the data that is to be returned
     * @param $data
     * @return $this
     */
    public function setBodyData($data)
    {
        if ($data instanceof \Generator) {
            foreach ($data as $key => $value) {
                $actualKey = $key ?: 'data';
                $this->body[$actualKey] = $value;
            }
            return $this;
        }
        $this->body['data'] = $data;
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
     * This allows you to manually set the formatter, however it is advisable to use setFormatFactor instead
     * @param Formatter $formatter
     * @return $this
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Format and prepare the response and save it for later
     * @return $this
     */
    public function prepareResponse()
    {
        if (!$this->formatter) {
            $this->formatter = $this->formatFactory->getFormatterFor(
                $this->request->getFormats()
            );
        }
        $this->preparedResponse =
            $this->formatter->getHeader()
            . $this->formatter->format($this->jsonSerialize(), $this->responseName)
            . $this->formatter->getFooter();
        return $this;
    }

    /**
     * Format the data and send as a response. Only one response can be sent
     * @return $this
     */
    public function respond()
    {
        if (is_null($this->preparedResponse)) {
            $this->prepareResponse();
        }

        if ($this->status instanceof Status) {
            header($this->status->getHttpHeader());
        }

        header("Content-Type: {$this->formatter->getContentType()}");
        echo $this->preparedResponse;

        return $this;
    }

    /**
     * Used by PHP to get json object
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getBody();
    }
}
