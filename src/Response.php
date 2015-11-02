<?php
/**
 * Response object
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer;

/**
 * Describes response to client
 * @package AyeAye\Api
 */
class Response
{

    /**
     * Used to name the data object that is returned to the user where applicable
     * @var string
     */
    protected $responseName = 'response';

    /**
     * Response format. Defaults to json
     * @var WriterFactory
     */
    protected $writerFactory;

    /**
     * The formatter object used to format this response
     * @var Writer
     */
    protected $writer;

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
     * @param WriterFactory $writerFactory
     * @return $this
     */
    public function setWriterFactory(WriterFactory $writerFactory)
    {
        $this->writerFactory = $writerFactory;
        return $this;
    }

    /**
     * This allows you to manually set the formatter, however it is advisable to use setFormatFactor instead
     * @param Writer $writer
     * @return $this
     */
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * Format and prepare the response and save it for later
     * @return $this
     */
    public function prepareResponse()
    {
        if (!$this->writer) {
            $this->writer = $this->writerFactory->getWriterFor(
                $this->request->getFormats()
            );
        }
        $this->preparedResponse = $this->writer->format($this->getBody(), $this->responseName);
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

        header("Content-Type: {$this->writer->getContentType()}");
        echo $this->preparedResponse;

        return $this;
    }
}
