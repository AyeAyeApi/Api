<?php
/**
 * LoggerInjector.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api\Injector;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Trait LoggerInjector
 * Allows the injection and management of a Psr/LoggerInterface object. Provides a NullLogger if one isn't set.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
trait LoggerInjector
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Set a PSR logger.
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get the previously set PSR logger.
     *
     * If a logger has not been set, the PSR NullLogger will be returned. This
     * can be used like any other logger but won't have any effect.
     *
     * @return NullLogger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }
}
