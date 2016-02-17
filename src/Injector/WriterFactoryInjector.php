<?php
/**
 * WriterFactoryInjector.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use AyeAye\Formatter\WriterFactory;
use AyeAye\Formatter\Writer\Json;
use AyeAye\Formatter\Writer\Xml;

/**
 * Trait WriterFactoryInjector
 * Allows the injection and management of a WriterFactory object. Provides a default if one isn't set.
 * Note: The default WriterFactory provides writers for Json and XML
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait WriterFactoryInjector
{
    /**
     * A collection of writers available
     * @var WriterFactory
     */
    private $writerFactory;

    /**
     * Sets the writer factory.
     *
     * Use for dependency injection.
     *
     * @param WriterFactory $writerFactory
     * @returns $this
     */
    public function setWriterFactory(WriterFactory $writerFactory)
    {
        $this->writerFactory = $writerFactory;
        return $this;
    }

    /**
     * Get the writer factory.
     *
     * If none is set it will create a default format factory for xml and json
     *
     * @return WriterFactory
     */
    public function getWriterFactory()
    {
        if (!$this->writerFactory) {
            $xmlFormatter = new Xml();
            $jsonFormatter = new Json();
            $this->writerFactory = new WriterFactory([
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
