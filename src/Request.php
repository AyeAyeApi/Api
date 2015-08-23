<?php
/**
 * A request option
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace AyeAye\Api;

/**
 * Describes every detail of a request to the server
 * @package AyeAye\Api
 */
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
    public $allowedMethods = [
        self::METHOD_GET,
        self::METHOD_HEAD,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE,
        self::METHOD_TRACE,
        self::METHOD_OPTIONS,
        self::METHOD_CONNECT,
        self::METHOD_PATCH,
    ];

    /**
     * The method of request
     * @var string
     */
    protected $requestMethod = null;

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
     * An amalgamation of all parameters sent in any way
     * @var array
     */
    protected $parameters = [];

    /**
     * Create a Request object. You can override any request information
     * @param string $requestedMethod
     * @param string $requestedUri
     * @param array|object ...$parameters Any number of arrays or objects containing request parameters
     *                                    such as _GET, _POST. If omitted, defaults will be used.
     */
    public function __construct(
        $requestedMethod = null,
        $requestedUri = null
    ) {
        $parameters = array_slice(func_get_args(), 2);
        foreach ($parameters as $parameterGroup) {
            $this->setParameters($parameterGroup);
        }

        $this->requestMethod = $requestedMethod ?: $this->getRequestMethod();
        $this->requestedUri  = $requestedUri    ?: $this->getRequestedUri();
        if (!$this->parameters) {
            $this->parameters = $this->useActualParameters();
        }
    }

    /**
     * Get the HTTP verb for this request
     * Checks it's one the API allows for. Can be overridden with override.
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getRequestMethod()
    {
        if($this->requestMethod) {
            return $this->requestMethod;
        }
        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return $_SERVER['REQUEST_METHOD'];
        }
        return static::METHOD_GET;
    }

    /**
     * Get the requested uri.
     * Can be overridden with override.
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getRequestedUri()
    {
        if($this->requestedUri) {
            return $this->requestedUri;
        }
        if(array_key_exists('REQUEST_URI', $_SERVER)) {
            return $_SERVER['REQUEST_URI'];
        }
        return '';
    }

    /**
     * Get parameters associated with this request.
     * Starts with _REQUEST super global, adds headers, then body, overriding in that order
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function useActualParameters()
    {
        $this->setParameters($this->urlToParameters($this->getRequestedUri()));
        $this->setParameters($_REQUEST);
        $this->setParameters($this->parseHeader($_SERVER));
        $this->setParameters($this->stringToObject($this->readBody()));
        return $this->getParameters();
    }

    /**
     * Parse headers
     * @param string[] $headers
     * @return string
     */
    protected function parseHeader(array $headers = [])
    {
        $processedHeaders = array();
        foreach ($headers as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $processedHeaders[$name] = $value;
            } elseif ($key == 'CONTENT_TYPE') {
                $processedHeaders['Content-Type'] = $value;
            } elseif ($key == 'CONTENT_LENGTH') {
                $processedHeaders['Content-Length'] = $value;
            }
        }
        return $processedHeaders;
    }

    /**
     * Reads in the body of the request
     * @return string
     */
    protected function readBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * Turns a url string into an array of parameters
     * @param string $url
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function urlToParameters($url)
    {
        $urlParameters = [];
        $url = parse_url($url, PHP_URL_PATH);
        $urlParts = explode('/', $url);
        reset($urlParts); // Note, the first entry will always be blank
        $key = next($urlParts);
        while (($value = next($urlParts)) !== false) {
            $urlParameters[$key] = $value;
            $key = $value;
        }
        return $urlParameters;
    }

    /**
     * Tries to turn a string of data into an object. Accepts json, xml or a php serialised object
     * Failing all else, if there was a string it will return a standard class with it attached to a 'text' attribute
     * eg. $this->stringObject('fail')->body == 'fail'
     * @param string $string a string of data
     * @return \stdClass
     */
    protected function stringToObject($string)
    {
        if (!$string) {
            return new \stdClass();
        }

        // Json
        $jsonObject = json_decode($string);
        if ($jsonObject) {
            return $jsonObject;
        }

        // Xml
        try {
            libxml_use_internal_errors();
            $xmlObject = simplexml_load_string($string);
            libxml_use_internal_errors(true);
            if ($xmlObject) {
                return $xmlObject;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        $object = new \stdClass();
        $object->text = $string;
        return $object;
    }

    /**
     * The http method being used
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Look for the given parameter anywhere in the request
     * @param string $key
     * @param bool $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        // Request _should_ contain get, post and cookies
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }
        // We can also flatten out the variable names to see if they exist
        $flatKey = $this->flatten($key);
        foreach ($this->parameters as $index => $value) {
            if ($flatKey == $this->flatten($index)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Flatten a variable name by removing all non alpha numeric characters and making it lower case
     * @param $name
     * @return string
     */
    protected function flatten($name)
    {
        return strtolower(preg_replace('/[\W_-]/', '', $name));
    }

    /**
     * Returns all parameters. Does not return header or body parameters, maybe it should
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the requested route
     * @return string[]
     */
    public function getRequestChain()
    {
        if (is_null($this->requestChain)) {
            $this->requestChain = $this->getRequestChainFromUri($this->requestedUri);
        }
        return $this->requestChain;
    }

    /**
     * The request could specify the desired response format in a number of ways, this returns them all
     * @return string
     */
    public function getFormats()
    {
        return [
            'header' => $this->getParameter('Accept'),
            'suffix' => $this->getFormatFromUri($this->requestedUri),
            'default' => static::DEFAULT_FORMAT,
        ];
    }

    /**
     * Used by PHP to get json object
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'method' => $this->getMethod(),
            'requestedUri' => $this->requestedUri,
            'parameters' => $this->getParameters()
        ];
    }

    /**
     * Get the format from the url
     * @param $requestedUri
     * @return string|null
     */
    protected function getFormatFromUri($requestedUri)
    {
        $uriParts = explode('?', $requestedUri, 2);
        $uriWithoutGet = reset($uriParts);
        $uriAndFormat = explode('.', $uriWithoutGet);
        if (count($uriAndFormat) >= 2) {
            return end($uriAndFormat);
        }
        return null;
    }

    /**
     * Breaks a url into useful parts
     * @param string $requestedUri
     * @return string[]
     */
    protected function getRequestChainFromUri($requestedUri)
    {
        // Trim any get variables and the requested format, eg: /requested/uri.format?get=variables
        $requestedUri = preg_replace('/[\?\.].*$/', '', $requestedUri);
        // Clear the base url

        $requestChain = explode('/', $requestedUri);

        if (!$requestChain[0]) {
            unset($requestChain[0]);
        }

        return array_values($requestChain);
    }

    /**
     * Add a set of parameters to the Request
     * @param array|object $newParameters
     * @throws \Exception
     * @returns $this
     */
    protected function setParameters($newParameters)
    {
        if (is_scalar($newParameters)) {
            if (!is_string($newParameters)) {
                throw new \Exception('newParameters can not be scalar');
            }
            $newParameters = $this->stringToObject($newParameters);
        }
        foreach ($newParameters as $field => $value) {
            $this->setParameter($field, $value);
        }
        return $this;
    }

    /**
     * Add a parameter
     * @param $name
     * @param $value
     * @return bool Returns true of value was set
     * @throws \Exception
     */
    protected function setParameter($name, $value)
    {
        if (!is_scalar($name)) {
            throw new \Exception('Parameter name must be scalar');
        }
        $this->parameters[$name] = $value;
        return $this;
    }
}
