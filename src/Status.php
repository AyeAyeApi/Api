<?php
/**
 * Represents an HTTP status, used to provide appropriate response
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

/**
 * Collection of HTTP statuses
 * @package AyeAye\Api
 */
class Status implements \JsonSerializable
{

	/**
	 * A list of common HTTP status codes and their associated messages
	 * @var array
	 */
    public static $statusCodes = [
        // Informational
        100 => 'Continue',
        // If a request has both a header and a body you can break it in two with this
        101 => 'Switching Protocols',
        // Let the client know you're switching protocols (not really necessary)
        102 => 'Processing',
        // Taken from WebDAV, used for requests that take a long time and might get queued

        // Success
        200 => 'OK',
        // Hopefully you'll see this a lot
        201 => 'Created',
        // A new record was successfully created
        202 => 'Accepted',
        // The request has been accepted but NOT yet processed
        203 => 'Non-Authoritative Information (since HTTP/1.1)',
        // Returned information might be from another source
        204 => 'No Content',
        // Requested processed but isn't returning any content
        205 => 'Reset Content',
        // Request processed but isn't returning any content, request user reset the document view
        206 => 'Partial Content',
        // Request contains partial content (if request larger than range requested by client)
        // 207 => 'Multi-Status', // WebDAV, lets just avoid this for now, it's a mess
        208 => 'Already Reported',
        // Taken from WebDAV. Could be use for an already implemented request
        226 => 'IM Used',
        // o.O

        // Redirect
        300 => 'Multiple Choices',
        // Multiple choices means the client must provide more information
        301 => 'Moved Permanently',
        // And don't come back!
        302 => 'Found',
        // Sometimes used to mean Moved Temporarily, however 307 should be used instead
        303 => 'See Other',
        // Like Moved Temporarily accept the resource should be hit with GET regardless of method used here
        304 => 'Not Modified',
        // Can be used to respond to the headers If-Modified-Since or If-Match
        305 => 'Use Proxy',
        // Provides the address of a proxy through with the request needs to be passed (eg, for security)
        // 306 => 'Switch Proxy', // No longer used, see above
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Like 301 but the redirect MUST use the same method. Not really used

        // Client Error
        400 => 'Bad Request',
        // Request is syntactically wrong
        401 => 'Unauthorized',
        // Not yet authorized
        402 => 'Payment Required',
        // Could be used to mean monetary, but could also mean a CAPTCHA
        403 => 'Forbidden',
        // Authorized but still don't have permission
        404 => 'Not Found',
        // I don't think I need to describe this... are you sure you're in the right business?
        405 => 'Method Not Allowed',
        // Using GET instead of PUT for example
        406 => 'Not Acceptable',
        // Response available but not appropriate based on the clients request headers
        407 => 'Proxy Authentication Required',
        // Authenticate with proxy first
        408 => 'Request Timeout',
        // Server took too long
        409 => 'Conflict',
        // Conflicting information in the request
        410 => 'Gone',
        // Requested resource is no longer available and will not be made available again
        411 => 'Length Required',
        // Request must specify the length of it's content (probably not useful in PHP)
        412 => 'Precondition Failed',
        // The server does not meet a precondition of the request
        413 => 'Request Entity Too Large',
        // Request is larger than the server is willing to deal with
        414 => 'Request-URI Too Long',
        // Uri is too long (probably not useful in PHP)
        415 => 'Unsupported Media Type',
        // The server does not support the requested response format (eg contact-list.png)
        416 => 'Requested Range Not Satisfiable',
        // For example, the client requested more data than is available
        417 => 'Expectation Failed',
        // Expect header requirements can't be et
        418 => 'I\'m a teapot',
        // Can not make coffee
        419 => 'Authentication Timeout',
        // Authentication has expired
        422 => 'Unprocessable Entity',
        // Taken from WebDAV, syntactically correct but semantically wrong request
        423 => 'Locked',
        // Taken from WebDAV, the resource has been locked (could be used like 410 Gone, but try again later)
        424 => 'Failed Dependency',
        // Taken from WebDAV, this request failed due to an earlier one
        426 => 'Upgrade Required',
        // Use a newer protocol (probably not useful in PHP, but could mean client out of date)
        428 => 'Precondition Required',
        // Ugh, not sure
        429 => 'Too Many Requests',
        // For rate limiting
        431 => 'Request Header Fields Too Large',
        // For
        440 => 'Login Timeout',
        // Taken from Microsoft, concise.
        444 => 'No Response',
        // Taken from Nginx, like 204 No Content, except it might be the clients fault
        449 => 'Retry With',
        // Taken from Microsoft, can be used to specify missing parameters
        450 => 'Inappropriate content',
        // Re-purposed from Microsoft.
        451 => 'Unavailable For Legal Reasons',
        // I get it :)
        495 => 'Certificate Error',
        // Taken from Nginx and re worded. Useful for certificate authentication
        496 => 'Certificate Required',
        // Taken from Nginx and re worded. Useful for certificate authentication
        498 => 'Token expired/invalid',
        // Taken from Esri. useful for OAuth or similar
        499 => 'Token required',
        // Taken from Esri. useful for OAuth or similar

        // Server Error
        500 => 'Internal Server Error',
        // Generic "something went wrong on our end"
        501 => 'Not Implemented',
        // Request method not implemented but might be in the future
        502 => 'Bad Gateway',
        // Server was acting as a gateway but received an invalid response from upstream
        503 => 'Service Unavailable',
        // Out to lunch, back soon
        504 => 'Gateway Timeout',
        // Server was acting as a gateway but did not receive a timely response from upstream
        505 => 'HTTP Version Not Supported',
        // Probably not useful in an PHP
        506 => 'Variant Also Negotiates',
        // o.O
        507 => 'Insufficient Storage',
        // Taken from WebDAV. Ran out of space
        508 => 'Loop Detected',
        // Taken from WebDAV. Like 208 but server has detected an infinite loop
        509 => 'Bandwidth Limit Exceeded',
        // Taken from Apache Bandwidth limit extension, but could be useful for throttling
        510 => 'Not Extended',
        // More extensions to request required?
        511 => 'Network Authentication Required',
        // Not going to be useful in PHP as you're already at the server, see 401
        520 => 'Origin Error',
        // Taken from Cloudflare. Resource provider sent an error. Use 502 instead
        521 => 'Web server is down',
        // Taken from Cloudflare. Can't connect to resource provider Use 503 instead
        522 => 'Connection timed out',
        // Taken from Cloudflare. Connection to resource provider timed out. Use 504 instead
        523 => 'Proxy Declined Request',
        // Taken from Cloudflare. Resource has been blocked. Use 401 or 403 instead
        524 => 'A timeout occurred',
        // Taken from Cloudflare. Connection to proxy timed out. Use 504 instead
        598 => 'Network read timeout error',
        // Taken from Microsoft. Connection to proxy timed out. Use 504 instead
        599 => 'Network connect timeout error',
        // Taken from Microsoft. Connection to proxy timed out. Use 504 instead
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
        $this->message = static::$statusCodes[$code];
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