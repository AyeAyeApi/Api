<?php
/**
 * Api.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

/**
 * Class Status
 * Represents an HTTP status, used to provide appropriate response
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Status implements \JsonSerializable
{
    /**
     * A list of common HTTP status codes and their associated messages
     * @var array
     */
    public static $statusCodes = [
        // Informational
        // If a request has both a header and a body you can break it in two with this
        100 => 'Continue',
        // Let the client know you're switching protocols (not really necessary)
        101 => 'Switching Protocols',
        // Taken from WebDAV, used for requests that take a long time and might get queued
        102 => 'Processing',

        // Success
        // Hopefully you'll see this a lot
        200 => 'OK',
        // A new record was successfully created
        201 => 'Created',
        // The request has been accepted but NOT yet processed
        202 => 'Accepted',
        // Returned information might be from another source
        203 => 'Non-Authoritative Information (since HTTP/1.1)',
        // Requested processed but isn't returning any content
        204 => 'No Content',
        // Request processed but isn't returning any content, request user reset the document view
        205 => 'Reset Content',
        // Request contains partial content (if request larger than range requested by client)
        206 => 'Partial Content',
        // 207 => 'Multi-Status', // WebDAV, lets just avoid this for now, it's a mess
        // Taken from WebDAV. Could be use for an already implemented request
        208 => 'Already Reported',
        // o.O
        226 => 'IM Used',

        // Redirect
        // Multiple choices means the client must provide more information
        300 => 'Multiple Choices',
        // And don't come back!
        301 => 'Moved Permanently',
        // Sometimes used to mean Moved Temporarily, however it's implemented link 'See Other'
        // If that's not desired 307 should be used instead
        302 => 'Found',
        // Like Moved Temporarily except the resource should be hit with GET regardless of method used here
        303 => 'See Other',
        // Can be used to respond to the headers If-Modified-Since or If-Match
        304 => 'Not Modified',
        // Provides the address of a proxy through with the request needs to be passed (eg, for security)
        305 => 'Use Proxy',
        // 306 => 'Switch Proxy', // No longer used, see above
        // Moved to a different location but uses the same verb
        307 => 'Temporary Redirect',
        // Like 301 but the redirect MUST use the same method. Not really used
        308 => 'Permanent Redirect',

        // Client Error
        // Request is syntactically wrong
        400 => 'Bad Request',
        // Not yet authorized
        401 => 'Unauthorized',
        // Could be used to mean monetary, but could also mean a CAPTCHA
        402 => 'Payment Required',
        // Authorized but still don't have permission
        403 => 'Forbidden',
        // I don't think I need to describe this... are you sure you're in the right business?
        404 => 'Not Found',
        // Using GET instead of PUT for example
        405 => 'Method Not Allowed',
        // Response available but not appropriate based on the clients request headers
        406 => 'Not Acceptable',
        // Authenticate with proxy first
        407 => 'Proxy Authentication Required',
        // Server took too long
        408 => 'Request Timeout',
        // Conflicting information in the request
        409 => 'Conflict',
        // Requested resource is no longer available and will not be made available again
        410 => 'Gone',
        // Request must specify the length of it's content (probably not useful in PHP)
        411 => 'Length Required',
        // The server does not meet a precondition of the request
        412 => 'Precondition Failed',
        // Request is larger than the server is willing to deal with
        413 => 'Request Entity Too Large',
        // Uri is too long (probably not useful in PHP)
        414 => 'Request-URI Too Long',
        // The server does not support the requested response format (eg contact-list.png)
        415 => 'Unsupported Media Type',
        // For example, the client requested more data than is available
        416 => 'Requested Range Not Satisfiable',
        // Expect header requirements can't be et
        417 => 'Expectation Failed',
        // Can not make coffee
        418 => 'I\'m a teapot',
        // Authentication has expired
        419 => 'Authentication Timeout',
        // Taken from WebDAV, syntactically correct but semantically wrong request
        422 => 'Unprocessable Entity',
        // Taken from WebDAV, the resource has been locked (could be used like 410 Gone, but try again later)
        423 => 'Locked',
        // Taken from WebDAV, this request failed due to an earlier one
        424 => 'Failed Dependency',
        // Use a newer protocol (probably not useful in PHP, but could mean client out of date)
        426 => 'Upgrade Required',
        // Ugh, not sure
        428 => 'Precondition Required',
        // For rate limiting
        429 => 'Too Many Requests',
        // For
        431 => 'Request Header Fields Too Large',
        // Taken from Microsoft, concise.
        440 => 'Login Timeout',
        // Taken from Nginx, like 204 No Content, except it might be the clients fault
        444 => 'No Response',
        // Taken from Microsoft, can be used to specify missing parameters
        449 => 'Retry With',
        // Re-purposed from Microsoft.
        450 => 'Inappropriate content',
        // I get it :)
        451 => 'Unavailable For Legal Reasons',
        // Taken from Nginx and re worded. Useful for certificate authentication
        495 => 'Certificate Error',
        // Taken from Nginx and re worded. Useful for certificate authentication
        496 => 'Certificate Required',
        // Taken from Esri. useful for OAuth or similar
        498 => 'Token expired/invalid',
        // Taken from Esri. useful for OAuth or similar
        499 => 'Token required',

        // Server Error
        // Generic "something went wrong on our end"
        500 => 'Internal Server Error',
        // Request method not implemented but might be in the future
        501 => 'Not Implemented',
        // Server was acting as a gateway but received an invalid response from upstream
        502 => 'Bad Gateway',
        // Out to lunch, back soon
        503 => 'Service Unavailable',
        // Server was acting as a gateway but did not receive a timely response from upstream
        504 => 'Gateway Timeout',
        // Probably not useful in an PHP
        505 => 'HTTP Version Not Supported',
        // o.O
        506 => 'Variant Also Negotiates',
        // Taken from WebDAV. Ran out of space
        507 => 'Insufficient Storage',
        // Taken from WebDAV. Like 208 but server has detected an infinite loop
        508 => 'Loop Detected',
        // Taken from Apache Bandwidth limit extension, but could be useful for throttling
        509 => 'Bandwidth Limit Exceeded',
        // More extensions to request required?
        510 => 'Not Extended',
        // Not going to be useful in PHP as you're already at the server, see 401
        511 => 'Network Authentication Required',
        // Taken from Cloudflare. Resource provider sent an error. Use 502 instead
        520 => 'Origin Error',
        // Taken from Cloudflare. Can't connect to resource provider Use 503 instead
        521 => 'Web server is down',
        // Taken from Cloudflare. Connection to resource provider timed out. Use 504 instead
        522 => 'Connection timed out',
        // Taken from Cloudflare. Resource has been blocked. Use 401 or 403 instead
        523 => 'Proxy Declined Request',
        // Taken from Cloudflare. Connection to proxy timed out. Use 504 instead
        524 => 'A timeout occurred',
        // Taken from Microsoft. Connection to proxy timed out. Use 504 instead
        598 => 'Network read timeout error',
        // Taken from Microsoft. Connection to proxy timed out. Use 504 instead
        599 => 'Network connect timeout error',
    ];

    /**
     * HTTP code for status
     * @var int
     */
    protected $code;

    /**
     * The message associated with the code
     * @var string
     */
    protected $message;

    /**
     * Construct a Status using an HTTP code
     * @param int $code Default 200
     * @throws \Exception If invalid code
     */
    public function __construct($code = 200)
    {
        if (!array_key_exists($code, static::$statusCodes)) {
            throw new \Exception("Status '$code' does not exist");
        }
        $this->code = $code;
        $this->message = static::getMessageForCode($code);
    }

    /**
     * HTTP code for status
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * HTTP code for status
     * @return int
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns data that is to be serialised by json
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Returns the appropriate message for a given code
     * @param int $code
     * @return null
     */
    public static function getMessageForCode($code)
    {
        if (array_key_exists($code, static::$statusCodes)) {
            return static::$statusCodes[$code];
        }
        return null;
    }

    /**
     * Send the header
     * @return $this
     */
    public function getHttpHeader()
    {
        return "HTTP/1.1 {$this->getCode()} {$this::getMessageForCode($this->getCode())}";
    }
}
