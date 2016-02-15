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

    const DEFAULT_DATA_NAME = 'data';

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
     * The formatted response.
     * We can prepare a response earlier to take advantage of the main API
     * class' error handling.
     * @var string
     */
    protected $preparedResponse;


    /**
     * Get all data that is being returned.
     *
     * This will return an array. Unless otherwise stated, data will usually be
     * attached to the 'data' key of this array.
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the data that is to be returned.
     *
     * If data is directly returned from an endpoint, it will be attached to
     * the default data name (usually "data").
     *
     * @example
     *   return 'hello world'; becomes { "data" => "hello world" }
     *
     * However, if a Generator is returned, the keys returned will be used
     * instead.
     *
     * @example
     *   yield 'hello' => 'world'; becomes { "hello" => "world" }
     *
     * If no key is given, the default will still be used. This is useful to
     * provide uniformity in response while adding additional data such as
     * HATEOAS.
     *
     * @example
     *   yield 'hello';
     *   yield 'links' => $this->generateLinks();
     *
     * @param $data
     * @return $this
     */
    public function setBodyData($data)
    {
        if ($data instanceof \Generator) {
            foreach ($data as $key => $value) {
                $actualKey = $key ?: static::DEFAULT_DATA_NAME;
                $this->body[$actualKey] = $value;
            }
            return $this;
        }
        $this->body[static::DEFAULT_DATA_NAME] = $data;
        return $this;
    }

    /**
     * Set the writer factory.
     *
     * This will be choose a writer to format the data based on the request.
     *
     * @param WriterFactory $writerFactory
     * @return $this
     */
    public function setWriterFactory(WriterFactory $writerFactory)
    {
        $this->writerFactory = $writerFactory;
        return $this;
    }

    /**
     * Set the writer.
     *
     * This overrides any choice that might otherwise be made by the writer
     * factory and should therefore be avoided.
     *
     * @param Writer $writer
     * @return $this
     */
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * Prepare the response.
     *
     * This selects a writer from the writer factory and prepares serialises
     * the data into a string. This is done early to allow exceptions and
     * errors to be caught and handled earlier.
     *
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
     * Send the response.
     *
     * This method sends headers and writes the data to the output stream.
     *
     * If the response has not yet been prepared, this method will prepare it
     * first. This method should be the last thing that happens before the
     * script ends.
     *
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
