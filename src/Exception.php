<?php
/**
 * Exception.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

/**
 * Class Exception
 * Designed to be thrown back to the API user.
 * Provides a public message and HTTP status code.
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
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
     * @param string $publicMessage Message to show the user if not caught. Optional
     * @param int $code HTTP Status code to send to the user
     * @param string $systemMessage Message to show the enter into the log if different from the public message
     * @param \Exception $previous Any previous Exception
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct($publicMessage = '', $code = 500, $systemMessage = '', \Exception $previous = null)
    {
        // Shift all parameters along if the first parameter is a string
        if (is_int($publicMessage)) {
            if (is_string($code)) {
                if ($systemMessage instanceof \Exception) {
                    $previous = $systemMessage;
                }
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
        if (!$systemMessage) {
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
        $serialized = [
            'message' => $this->getPublicMessage(),
            'code' => $this->getCode(),
        ];
        if ($this->getPrevious() instanceof $this) {
            /** @var static $previous */
            $previous = $this->getPrevious();
            $serialized['previous'] = $previous->jsonSerialize();
        }
        return $serialized;
    }
}
