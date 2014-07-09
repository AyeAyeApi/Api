<?php
/**
 * A request option
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Api;

class Request
{

    // HTTP verbs as defined in http://www.ietf.org/rfc/rfc2616
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_TRACE = 'TRACE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH = 'PATCH';

    /**
     * A list of accepted HTTP verbs. By default everything is accepted
     * however, you could extend Request and provide a different list.
     * @var array
     */
    public static $allowedMethods = array(
        self::METHOD_GET,
        self::METHOD_HEAD,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE,
        self::METHOD_TRACE,
        self::METHOD_OPTIONS,
        self::METHOD_CONNECT,
        self::METHOD_PATCH,
    );

    /**
     * The requested uri as an array
     * @var array
     */
    protected $requestChain = array();

    /**
     * The format for requested data
     * @var string Defaults to 'json'
     */
    protected $requestedFormat = Response::FORMAT_JSON;

    /**
     * @var array
     */
    protected $get = array();

    /**
     * @var array
     */
    protected $post = array();

    /**
     * @var array
     */
    protected $cookie = array();

    /**
     * @var array
     */
    protected $request = array();

    /**
     * @var array
     */
    protected $header = array();

    /**
     * @var \stdClass
     */
    protected $body = null;

    /**
     * Create a Request object. You can override any request information
     * @param string $requestedUri
     * @param string $requestedMethod
     * @param array $request
     * @param array $header
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param string $bodyText
     */
    public function __construct(
        $requestedUri = '',
        $requestedMethod = self::METHOD_GET,
        array $request = array(),
        array $header = array(),
        array $get = array(),
        array $post = array(),
        array $cookie = array(),
        $bodyText = ''

    ) {

        // Trim any get variables and the requested format, eg: /requested/uri.format?get=variables
        $requestedUriAndFormat  = explode('.', array_shift(explode('?', $requestedUri)));
        $requestedFormat = end($requestedUriAndFormat); // Check the last element but don't remove it
        if(in_array($requestedFormat, Response::$acceptableFormats)) {
            $this->requestedFormat = $requestedFormat;
        }
        $this->requestChain = explode('/', reset($requestedUriAndFormat));

        // Pull together all of the variables
        $this->request = $request ? $request : $_REQUEST;
        $this->header  = $header  ? $header  : $this->parseHeader();
        $this->get     = $get     ? $get     : $_GET;
        $this->post    = $post    ? $post    : $_POST;
        $this->cookie  = $cookie  ? $cookie  : $_COOKIE;
        if(!$bodyText) {
            $bodyText = $this->readBody();
        }
        $this->body = $this->stringToObject($bodyText);
    }

    /**
     * Get the request headers
     * Note: Should be ok with Apache and Nginx
     * @return array
     */
    protected function parseHeader() {
        if(function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $this->header[$name] = $value;
            }
            elseif ($key == 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            }
            elseif ($key == 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }
        return $headers;
    }

    /**
     * Reads in the body of the request
     * @return string
     */
    protected function readBody() {
        if(function_exists('http_get_request_body')) {
            return http_get_request_body();
        }
        return @file_get_contents('php://input');
    }

    /**
     * Tries to turn a string of data into an object. Accepts json, xml or a php serialised object
     * Failing all else it will return a standard class with the string attached to data
     * eg. $this->stringObject('fail')->data == 'fail'
     * @param string $string a string of data
     * @return \stdClass
     */
    protected function stringToObject($string) {
        // Json
        if($jsonObject = json_decode($string)) {
            return (object)$jsonObject;
        }
        // Xml
        if($xmlObject = simplexml_load_string($string)) {
            return $xmlObject;
        }
        // Php
        if($phpObject = unserialize($string)) {
            return $phpObject;
        }

        $object = new \stdClass();
        $object->data = $string;
        return $object;
    }

    /**
     * Look for the given parameter anywhere in the request
     * @param string $key
     * @param bool $default
     * @return mixed
     */
    public function getParameter($key, $default = false) {
        // Request _should_ contain get, post and cookies however we will
        // still check for these at the end of the function
        if(array_key_exists($key, $this->request)) {
            return $this->request[$key];
        }
        if(array_key_exists($key, $this->header)) {
            return $this->header[$key];
        }
        if(property_exists($this->body, $key)) {
            return $this->body->$key;
        }
        if(array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        if(array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if(array_key_exists($key, $this->cookie)) {
            return $this->cookie[$key];
        }
        return $default;
    }

} 