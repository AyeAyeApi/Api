<?php

namespace AyeAye\Api\Injector;

use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer\Json;
use AyeAye\Formatter\Writer\Xml;

trait WriterFactoryInjector
{

    /**
     * A collection of writers available
     * @var WriterFactory
     */
    private $writerFactory;

    /**
     * Sets the format factory. Use for dependency injection, or additional formatters
     * @param WriterFactory $writerFactory
     * @returns $this
     */
    public function setWriterFactory(WriterFactory $writerFactory)
    {
        $this->writerFactory = $writerFactory;
        return $this;
    }

    /**
     * Get the format factory. If none is set it will create a default format factory for xml and json
     * @return WriterFactory
     */
    public function getWriterFactory()
    {
        if (!$this->writerFactory) {
            $xmlFormatter = new Xml();
            $jsonFormatter = new Json();
            $this->writerFactory = new \AyeAye\Formatter\WriterFactory([
                // xml
                'xml' => $xmlFormatter,
                'text/xml' => $xmlFormatter,
                'application/xml' => $xmlFormatter,
                // json
                'json' => $jsonFormatter,
                'application/json' => $jsonFormatter,
            ]);
        }
        return $this->writerFactory;
    }
}
