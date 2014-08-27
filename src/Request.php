<?php
/**
 * A request option
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;

class Request implements \JsonSerializable
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

    const DEFAULT_FORMAT = 'json';

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
     * The method of request
     * @var string
     */
    protected $requestMethod = self::METHOD_GET;

    /**
     * @var string
     */
    protected $requestedUri = '';

    /**
     * The requested uri as an array
     * @var array
     */
    protected $requestChain = null;

    /**
     * The format for requested data
     * @var string Defaults to 'json'
     */
    protected $requestedFormat;

    /**
     * @var array
     */
    protected $parameters = array();

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
     * @param string $requestedMethod
     * @param string $requestedUri
     * @param array $request
     * @param array $header
     * @param string $bodyText
     */
    public function __construct(
        $requestedMethod = null,
        $requestedUri = '',
        array $request = array(),
        array $header = array(),
        $bodyText = ''
    ) {

        if($requestedMethod) {
            $this->requestMethod = $requestedMethod;
        }
        elseif(array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        if($requestedUri) {
            $this->requestedUri = $requestedUri;
        }
        elseif(array_key_exists('REQUEST_URI', $_SERVER)) {
            $this->requestedUri = $_SERVER['REQUEST_URI'];
        }

        // Pull together all of the variables
        $this->parameters = $request ? $request : $_REQUEST;
        $this->header  = $header  ? $this->parseHeader($header) : $this->parseHeader($_SERVER);
        if(!$bodyText) {
            $bodyText = $this->readBody();
        }
        $this->body = $this->stringToObject($bodyText);
    }

    /**
     * Parse headers
     * @param string[] $headers
     * @return string
     */
    public function parseHeader(array $headers = array()) {
        $processedHeaders = array();
        foreach($headers as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $processedHeaders[$name] = $value;
            }
            elseif ($key == 'CONTENT_TYPE') {
                $processedHeaders['Content-Type'] = $value;
            }
            elseif ($key == 'CONTENT_LENGTH') {
                $processedHeaders['Content-Length'] = $value;
            }
        }
        return $processedHeaders;
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
     * eg. $this->stringObject('fail')->body == 'fail'
     * @param string $string a string of data
     * @throws \Exception
     * @return \stdClass
     */
    public function stringToObject($string) {
        if(!is_string($string)) {
            throw new \Exception('Non-string passed to stringToObject');
        }
        // Json
        if($jsonObject = json_decode($string)) {
            return $jsonObject;
        }
        // Xml
        if($xmlObject = @simplexml_load_string($string)) {
            return $xmlObject;
        }
        // Php
        if($phpObject = @unserialize($string)) {
            return $phpObject;
        }

        $object = new \stdClass();
        $object->body = $string;
        return $object;
    }

    /**
     * The http method being used
     * @return string
     */
    public function getMethod() {
        return $this->requestMethod;
    }

    /**
     * Look for the given parameter anywhere in the request
     * @param string $key
     * @param bool $default
     * @return mixed
     */
    public function getParameter($key, $default = null) {
        // Request _should_ contain get, post and cookies
        if(array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        if(array_key_exists($key, $this->header)) {
            return $this->header[$key];
        }
        if(property_exists($this->body, $key)) {
            return $this->body->$key;
        }
        return $default;
    }

    /**
     * Returns all parameters. Does not return header or body parameters, maybe it should
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Get the requested route
     * @return string[]
     */
    public function getRequestChain() {
        if(is_null($this->requestChain)) {
            $this->getRequestChainFromUri($this->requestedUri);
        }
        return $this->requestChain;
    }

    /**
     * Gets the expected response format
     * @return string
     */
    public function getFormat() {
        if(is_null($this->requestedFormat)) {
            $this->requestedFormat = $this->getFormatFromUri($this->requestedUri);
            if(is_null($this->requestedFormat)) {
                $this->requestedFormat = static::DEFAULT_FORMAT;
            }
        }
        return $this->requestedFormat;
    }

    /**
     * Used by PHP to get json object
     * @return array|mixed
     */
    public function jsonSerialize() {
        return [
            'method' => $this->getMethod(),
            'requestUri' => $this->requestedUri,
            'parameters' => $this->getParameters()
        ];
    }

    /**
     * Get the format from the url
     * @param $requestedUri
     * @return string|null
     */
    public function getFormatFromUri($requestedUri) {
        $uriParts = explode('?', $requestedUri, 2);
        $uriWithoutGet = reset($uriParts);
        $uriAndFormat  = explode('.', $uriWithoutGet);
        if(count($uriAndFormat) >= 2) {
            return end($uriAndFormat);
        }
        return null;
    }

    /**
     * Breaks a url into useful parts
     * @param string $requestedUri
     * @return string[]
     */
    protected function getRequestChainFromUri($requestedUri) {
        // Trim any get variables and the requested format, eg: /requested/uri.format?get=variables
        $routingInformation = preg_replace('/[\?\.].*$/', '', $requestedUri);
        $requestChain = explode('/', $routingInformation);
        if(!$requestChain[0]) {
            unset($requestChain[0]);
        }
        return $requestChain;
    }

}