<?php

/**
 * An exception specifically for API use.
 * Messages are hidden from the user if the Exception is not caught, a default
 * one set by the code is used instead
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

/**
 * Used to give the context of HTTP status to an Exception
 * @package AyeAye\Api
 */
class Exception extends \Exception implements \JsonSerializable
{

    const DEFAULT_ERROR_CODE = 500;
    const DEFAULT_MESSAGE = 'Internal Server Error';

    /**
	 * A message to show the client if available
     * @var string
     */
    public $publicMessage;

    /**
	 * Create a new Exception, include information to pass to the client
     * @param string $message Message to put into the log
     * @param int $code HTTP Status code to send to the user
     * @param string $publicMessage Message to show the user if different from the message associated with the status code
     * @param \Exception $previous Any previous Exception
     */
    public function __construct($message, $code = 500, $publicMessage = '', \Exception $previous = null)
    {
        $this->publicMessage = $publicMessage;
        if (!$publicMessage) {
            $this->publicMessage = Status::getMessageForCode($code);
            if (!$this->publicMessage) {
                $this->publicMessage = static::DEFAULT_MESSAGE;
            }
        }
        parent::__construct($message, $code, $previous);
    }

	/**
	 * Get the message to tell the client
	 * @return string
	 */
    public function getPublicMessage()
    {
        return $this->publicMessage;
    }

	/**
	 * Return data to be serialised into Json
	 * @return array
	 */
    public function jsonSerialize()
    {
        return [
            'message' => $this->getPublicMessage(),
            'code' => $this->getCode(),
            'previous' => $this->getPrevious(),
        ];
    }

} 