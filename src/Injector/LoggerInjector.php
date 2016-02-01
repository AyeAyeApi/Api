<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01/02/2016
 * Time: 20:56
 */

namespace AyeAye\Api\Injector;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LoggerInjector
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
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
