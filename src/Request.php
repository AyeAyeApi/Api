<?php
/**
 * Request.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Formatter\Reader\Json;
use AyeAye\Formatter\Reader\Xml;
use AyeAye\Formatter\ReaderFactory;

/**
 * Class Request.
 *
 * Describes the details of a request from the client.
 *
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
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
     * What uri did the client request
     * @var string
     */
    protected $requestedUri = '';

    /**
     * The requested uri as an array.
     * @var array
     */
    protected $requestChain = null;

    /**
     * An amalgamation of all parameters sent in any way.
     * @var array
     */
    protected $parameters = [];

    /**
     * The reader factory contains readers that might be able to read the body
     * @var ReaderFactory
     */
    protected $readerFactory;

    /**
     * Request constructor.
     *
     * To take the request as is from PHP, instantiate this class without
     * specifying and arguments. The constructor can be used to immediately
     * override any part of the request or even be used to arbitrarily define
     * request parameters.
     *
     * Note: If any parameters are defined, this will block the actual request
     * parameters from being used.
     *
     * @param string $requestedMethod
     * @param string $requestedUri
     * @param ReaderFactory $readerFactory
     * @param array|object ...$parameters Any number of arrays or objects containing request parameters such as _GET,
     *                                   _POST. This will override the defaults which will otherwise be taken from the
     *                                   url and _REQUEST global variable.
     */
    public function __construct(
        $requestedMethod = null,
        $requestedUri = null,
        ReaderFactory $readerFactory = null
    ) {
        $this->readerFactory = $readerFactory;

        $parameters = array_slice(func_get_args(), 3);
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
     * Get the reader factory.
     *
     * If none is set, then create one, set it and return it. By default it
     * will only be able to read Xml and Json.
     *
     * @return ReaderFactory
     */
    protected function getReaderFactory()
    {
        if (!$this->readerFactory) {
            $this->readerFactory = new ReaderFactory([
                new Json(),
                new Xml(),
            ]);
        }
        return $this->readerFactory;
    }

    /**
     * Get the HTTP verb for this request.
     *
     * The actual verb can be overridden simply by setting it in the
     * constructor. If no verb is set, and for some reason it can not be found
     * in the _SERVER global variable, a default of 'GET' will be returned.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return string
     */
    protected function getRequestMethod()
    {
        if ($this->requestMethod) {
            return $this->requestMethod;
        }
        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            return $_SERVER['REQUEST_METHOD'];
        }
        return static::METHOD_GET;
    }

    /**
     * Get the requested uri.
     *
     * The actual uri can be overridden simply by setting it in the
     * constructor. If no uri is set, and for some reason it can not be found
     * in the _SERVER global variable, a default of '' will be returned.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return string
     */
    protected function getRequestedUri()
    {
        if ($this->requestedUri) {
            return $this->requestedUri;
        }
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            return $_SERVER['REQUEST_URI'];
        }
        return '';
    }

    /**
     * Get parameters associated with this request.
     *
     * Starts the url itself, then the _REQUEST super global, adds headers,
     * then body, overriding in that order (i.e. if a body variable overrides
     * a header, the body will be used instead).
     *
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function useActualParameters()
    {
        $this->setParameters($this->urlToParameters($this->getRequestedUri()));
        $this->setParameters($_REQUEST);
        $this->setParameters($this->parseHeader($_SERVER));
        $this->setParameters($this->stringToArray($this->readBody()));
        return $this->getParameters();
    }

    /**
     * Parse headers.
     *
     * This method should be passed the _SERVER global array. Most http headers
     * in PHP are prefixed with HTTP_ however, there are two values prefixed
     * with CONTENT_. All other values in the array will be ignored.
     *
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
     * Reads in the body of the request.
     *
     * Uses php streams to get the body of a request.
     *
     * @return string
     */
    protected function readBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * Turns a url string into an array of parameters.
     *
     * This can be a little bit confusing but it allows people to create
     * pretty uri's for an API. This array simply parametrises the uri
     * by assuming each slug is the key for the following one.
     *
     * @example 'here/is/an/example' => ['here' => 'is', 'is' => 'an', 'an' => 'example']
     *
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
     * Tries to turn a string of data into an array.
     *
     * This method uses a ReaderFactory that will attempt to read the sting
     * as a number of predefined strings, accepting the first one that returns
     * something. Failing all else, if there was a string it will return an
     * array with the text attached to a 'text' key
     * eg. $this->stringToArray('fail')['body'] == 'fail'
     *
     * @param string $string
     * @return array
     */
    protected function stringToArray($string)
    {
        if (!$string || !is_string($string)) {
            return [];
        }

        $result = $this->getReaderFactory()->read($string);
        if ($result) {
            return $result;
        }

        $array = [];
        $array['text'] = $string;
        return $array;
    }

    /**
     * The http method being used.
     *
     * Get the http verb used for the request.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Look up the value of a requested parameter.
     *
     * A default can be specified. If the parameter is not found the default
     * will be returned. The 'default' defaults to null.
     *
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
     * Flatten a variable name.
     *
     * Simply removes all non alpha numeric characters (except hyphen and
     * underscore) and makes it lower case.
     *
     * @param $name
     * @return string
     */
    protected function flatten($name)
    {
        return strtolower(preg_replace('/[\W_-]/', '', $name));
    }

    /**
     * Returns all parameters.
     *
     * This method should only be used for debugging.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the requested route.
     *
     * Breaks the uri into an array that can be used by the router to determine
     * which controller and endpoint should be called.
     *
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
     * Returns an array of possible formats.
     *
     * The request could specify the desired response format in a number of
     * ways, this method returns them all including a final default fallback.
     *
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
     * Used by PHP to get json object.
     *
     * This method is useful for debugging incoming requests. It should not
     * reveal anything the sender does not know, but might help explain how
     * the request has been processed.
     *
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
     * Get the format from the url.
     *
     * If the request had a suffix in the url, this method will discover it and
     * return it.
     *
     * @example /hello-world.json => json
     * @example /hello-world.xml => xml
     * @example /hello-world => null
     *
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
     * Breaks up the uri.
     *
     * Breaks the uri into slugs used to work out the route.
     *
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
     * Add a set of parameters to the Request.
     *
     * This method is for internal use only. It allows setting of key => value
     * parameters, including for string objects (assuming a Reader has been set
     * up for them).
     *
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
            $newParameters = $this->stringToArray($newParameters);
        }
        foreach ($newParameters as $field => $value) {
            $this->setParameter($field, $value);
        }
        return $this;
    }

    /**
     * Add a parameter
     *
     * Adds a single parameter, checking the parameter name is scalar.
     *
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
