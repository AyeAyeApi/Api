<?php

/**
 * An exception specifically for API use.
 * Messages are hidden from the user if the Exception is not caught, a default
 * one set by the code is used instead
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;

class Exception extends \Exception implements \JsonSerializable
{

    const DEFAULT_ERROR_CODE = 500;
    const DEFAULT_MESSAGE = 'Internal Server Error';

    protected $logMessage = '';

    /**
     * @param string $logMessage Message to put into the log
     * @param int $code HTTP Status code to send to the user
     * @param string $publicMessage Message to show the user if different from the message associated with the status code
     * @param \Exception $previous Any previous Exception
     */
    public function __construct($logMessage, $code = 500, $publicMessage = '', \Exception $previous = null) {
        $this->logMessage = $logMessage;
        if(!$publicMessage) {
            if(in_array($code, Status::$statusCodes)) {
                $publicMessage = Status::$statusCodes[$code];
            }
            else {
                $code = static::DEFAULT_ERROR_CODE;
                $publicMessage = static::DEFAULT_MESSAGE;
            }
        }
        parent::__construct($publicMessage, $code, $previous);
    }

    public function getLogMessage() {
        return $this->logMessage;
    }

    public function jsonSerialize() {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'previous' => $this->getPrevious(),
        ];
    }

} 