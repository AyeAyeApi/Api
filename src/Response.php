<?php
/**
 * Response.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\RequestInjector;
use AyeAye\Api\Injector\StatusInjector;
use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer;

/**
 * Class Response.
 *
 * Contains a payload of information to return to the client.
 *
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Response
{
    use StatusInjector;
    use RequestInjector;

    /**
     * The name of the object that is returned to the user where appropriate.
     *
     * @var string
     */
    protected $responseName = 'response';

    /**
     * The writer factory to use when the response is to be formatted
     * @var WriterFactory
     */
    protected $writerFactory;

    /**
     * The specific writer that will be used to format this response
     * @var Writer
     */
    protected $writer;

    /**
     * The data you wish to return in the response.
     * @var mixed
     */
    protected $body = [];

    /**
     * @var string
     */
    protected $preparedResponse;

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
