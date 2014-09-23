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
     * @param string $publicMessage Message to show the user if not caught. Can be omitted so long as an integer code is used instead
     * @param int $code HTTP Status code to send to the user
     * @param string $systemMessage Message to show the enter into the log if different from the public message
     * @param \Exception $previous Any previous Exception
     */
    public function __construct($publicMessage = '', $code = 500, $systemMessage = '', \Exception $previous = null)
    {
        if(is_int($publicMessage)) {
            if(is_string($code)) {
                $systemMessage = $code;
            }
            $code = $publicMessage;
            $publicMessage = null;
        }

        // If a public message wasn't specified, get it from the code
        if (!$publicMessage) {
            $publicMessage = Status::getMessageForCode($code);
            if (!$publicMessage) {
                $publicMessage = static::DEFAULT_MESSAGE;
            }
        }

        // If the system message wasn't specified, use the public message
        if(!$systemMessage) {
            $systemMessage = $publicMessage;
        }

        $this->publicMessage = $publicMessage;

        parent::__construct($systemMessage, $code, $previous);
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