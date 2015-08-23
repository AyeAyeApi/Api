<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:23
 */

namespace AyeAye\Api\Tests;

use AyeAye\Api\Request;

/**
 * Class ControllerTest
 * @package AyeAye\Api\Tests
 * @coversDefaultClass \AyeAye\Api\Request
 */
class RequestTest extends TestCase
{

    /**
     * @test
     * @covers ::__construct
     * @uses \AyeAye\Api\Request
     */
    public function testConstruct()
    {
        $request = new Request();
        $this->assertEmpty(
            $request->getParameters()
        );

        $parameters = ['test' => 'testString'];
        $request = new Request(null, null, ['test' => 'testString']);
        $this->assertSame(
            $parameters,
            $request->getParameters()
        );
    }

    /**
     * @test
     * @covers ::getRequestMethod
     * @uses \AyeAye\Api\Request
     * @backupGlobals
     */
    public function testGetRequestMethod()
    {
        $tempServer = $_SERVER;

        $request = new Request();
        $getRequestMethod = $this->getObjectMethod($request, 'getRequestMethod');
        $this->assertSame(
            Request::METHOD_GET,
            $getRequestMethod()
        );

        $request = new Request(Request::METHOD_HEAD);
        $getRequestMethod = $this->getObjectMethod($request, 'getRequestMethod');
        $this->assertSame(
            Request::METHOD_HEAD,
            $getRequestMethod()
        );

        $_SERVER['REQUEST_METHOD'] = Request::METHOD_POST;
        $request = new Request();
        $getRequestMethod = $this->getObjectMethod($request, 'getRequestMethod');
        $this->assertSame(
            Request::METHOD_POST,
            $getRequestMethod()
        );

        $_SERVER = $tempServer;
    }

    /**
     * @test
     * @covers ::getRequestedUri
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequestedUri()
    {
        $tempServer = $_SERVER;


        $request = new Request();
        $getRequestedUri = $this->getObjectMethod($request, 'getRequestedUri');

        $this->assertSame(
            '',
            $getRequestedUri()
        );

        $request = new Request();
        $getRequestedUri = $this->getObjectMethod($request, 'getRequestedUri');
        $_SERVER['REQUEST_URI'] = '/test';
        $this->assertSame(
            '/test',
            $getRequestedUri()
        );

        $url = '/anotherTest#anchor?url=parameter';
        $request = new Request(Request::METHOD_GET, $url);
        $getRequestedUri = $this->getObjectMethod($request, 'getRequestedUri');
        $this->assertSame(
            $url,
            $getRequestedUri($url)
        );

        $_SERVER = $tempServer;
    }

    /**
     * @test
     * @covers ::useActualParameters
     * @uses \AyeAye\Api\Request
     */
    public function testUseActualParameters()
    {
        $tempServer = $_SERVER;
        $tempRequest = $_REQUEST;

        $request = new Request();

        $useActualParameters = $this->getObjectMethod($request, 'useActualParameters');
        $parameters = $useActualParameters();

        $this->assertSame(
            [],
            $parameters
        );

        $url = '/url/value';
        $_SERVER['HTTP_ACCEPT'] = 'value';
        $_REQUEST['request'] = 'value';

        $request = new Request('GET', $url);

        $useActualParameters = $this->getObjectMethod($request, 'useActualParameters');
        $parameters = $useActualParameters();

        $this->assertSame(
            [
                'url' => 'value',
                'request' => 'value',
                'Accept' => 'value',
            ],
            $parameters
        );

        $_SERVER = $tempServer;
        $_REQUEST = $tempRequest;
    }

    /**
     * @test
     * @covers ::parseHeader
     * @uses \AyeAye\Api\Request
     */
    public function testParseHeader()
    {
        $request = new Request();
        $parseHeader = $this->getObjectMethod($request, 'parseHeader');

        $this->assertSame(
            [],
            $parseHeader()
        );

        $headers = [
            'CONTENT_TYPE' => 'content type',
            'CONTENT_LENGTH' => 'content length',
            'HTTP_MULTI_WORD' => 'other value',
        ];

        $this->assertSame(
            [
                'Content-Type' => 'content type',
                'Content-Length' => 'content length',
                'Multi-Word' => 'other value',
            ],
            $parseHeader($headers)
        );
    }

    /**
     * @test
     * @covers ::readBody
     * @uses \AyeAye\Api\Request
     */
    public function testReadBody()
    {
        $request = new Request();
        $readBody = $this->getObjectMethod($request, 'readBody');
        $this->assertSame(
            '',
            $readBody()
        );
    }

    /**
     * @test
     * @covers ::urlToParameters
     * @uses \AyeAye\Api\Request
     */
    public function testUrlToParameters()
    {
        $request = new Request();
        $urlToParameters = $this->getObjectMethod($request, 'urlToParameters');

        $url = '/someTest/album/42#anchor?something=true';
        $expected = [
            'someTest' => 'album',
            'album' => '42',
        ];
        $this->assertSame(
            $expected,
            $urlToParameters($url)
        );
    }

    /**
     * @test
     * @covers ::stringToObject
     * @uses \AyeAye\Api\Request
     */
    public function testStringToClass()
    {
        $request = new Request();
        $stringToObject = $this->getObjectMethod($request, 'stringToObject');


        $this->assertObjectNotHasAttribute(
            'text',
            $stringToObject('')
        );

        $this->assertObjectHasAttribute(
            'text',
            $stringToObject('test')
        );
        $this->assertSame(
            'test',
            $stringToObject('test')->text
        );

        $json = '{"key":"value"}';

        $this->assertObjectNotHasAttribute(
            'text',
            $stringToObject($json)
        );
        $this->assertObjectHasAttribute(
            'key',
            $stringToObject($json)
        );
        $this->assertSame(
            'value',
            $stringToObject($json)->key
        );

        $xml = '<container><key>anotherValue</key></container>';

        $this->assertObjectNotHasAttribute(
            'text',
            $stringToObject($xml)
        );
        $this->assertObjectHasAttribute(
            'key',
            $stringToObject($xml)
        );

        // ToDo: How does this even???
//        /** @var \SimpleXMLElement $object */
//        $object = $stringToObject($xml);
//        print_r($object->children()->text()); die;
//        $this->assertSame(
//            'anotherValue',
//            $stringToObject($xml)->key[0]
//        );
    }

    /**
     * @test
     * @covers ::getMethod
     * @uses \AyeAye\Api\Request
     */
    public function testGetMethod()
    {
        $request = new Request();
        $this->assertSame(
            'GET',
            $request->getMethod()
        );

        $request = new Request(Request::METHOD_POST);
        $this->assertSame(
            'POST',
            $request->getMethod()
        );
    }

    /**
     * @test
     * @covers ::getParameter
     * @uses \AyeAye\Api\Request
     */
    public function testGetParameter()
    {
        $request = new Request(null, null, [
            'key' => 'value1',
            'Key' => 'value2'
        ]);

        $this->assertSame(
            'value1',
            $request->getParameter('key', 'default')
        );

        $this->assertSame(
            'value2',
            $request->getParameter('Key', 'default')
        );

        $this->assertSame(
            'value1',
            $request->getParameter('KEY', 'default')
        );

        $this->assertSame(
            'default',
            $request->getParameter('KEYZ', 'default')
        );
    }

    /**
     * @test
     * @covers ::flatten
     * @uses \AyeAye\Api\Request
     */
    public function testFlatten()
    {
        $request = new Request();
        $flatten = $this->getObjectMethod($request, 'flatten');

        $this->assertSame(
            'thequickbrownfox',
            $flatten('The_Quick Brown-fox!')
        );
    }


    /**
     * @test
     * @covers ::getParameters
     * @uses \AyeAye\Api\Request
     */
    public function testGetParameters()
    {
        $request = new Request();
        $this->assertSame(
            [],
            $request->getParameters()
        );

        $expected = ['key' => 'value', 'alpha' => 'beta'];
        $request = new Request(null, null, $expected);
        $this->assertSame(
            $expected,
            $request->getParameters()
        );
    }

    /**
     * @test
     * @covers ::getRequestChain
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequestChain()
    {
        $request = new Request();

        $this->assertNull(
            $this->getObjectAttribute($request, 'requestChain')
        );
        $this->assertSame(
            [],
            $request->getRequestChain()
        );

        $request = new Request(null, '/test/chain');

        $this->assertSame(
            ['test', 'chain'],
            $request->getRequestChain()
        );
    }

    /**
     * @test
     * @covers ::getFormats
     * @uses \AyeAye\Api\Request
     */
    public function testGetFormats()
    {
        $tempSever = $_SERVER;

        $request = new Request();

        $this->assertSame(
            [
                'header' => null,
                'suffix' => null,
                'default' => 'json',
            ],
            $request->getFormats()
        );

        $_SERVER = $tempSever;
    }

    /**
     * @test
     * @covers ::jsonSerialize
     * @uses \AyeAye\Api\Request
     */
    public function testJsonSerialize()
    {
        $request = new Request();

        $this->assertSame(
            [
                'method' => Request::METHOD_GET,
                'requestedUri' => '',
                'parameters' => [],
            ],
            $request->jsonSerialize()
        );
    }

    /**
     * @test
     * @covers ::getFormatFromUri
     * @uses \AyeAye\Api\Request
     */
    public function testGetFormatFromUri()
    {
        $request = new Request();
        $getFormatFromUri = $this->getObjectMethod($request, 'getFormatFromUri');

        $this->assertNull(
            $getFormatFromUri('')
        );

        $this->assertNull(
            $getFormatFromUri('json')
        );

        $this->assertSame(
            'json',
            $getFormatFromUri('resource.json')
        );

        $this->assertSame(
            'json',
            $getFormatFromUri('resource.json?get=stuff')
        );
    }

    /**
     * @test
     * @covers ::getRequestChainFromUri
     * @uses \AyeAye\Api\Request
     */
    public function testGetRequestChainFromUri()
    {
        $request = new Request();
        $getRequestChainFromUri = $this->getObjectMethod($request, 'getRequestChainFromUri');

        $this->assertSame(
            [],
            $getRequestChainFromUri('')
        );

        $this->assertSame(
            ['one', 'two'],
            $getRequestChainFromUri('one/two')
        );

        $this->assertSame(
            ['one', 'two'],
            $getRequestChainFromUri('/one/two')
        );
    }

    /**
     * @test
     * @covers ::setParameters
     * @uses \AyeAye\Api\Request
     */
    public function testSetParameters()
    {
        $request = new Request();
        $setParameters = $this->getObjectMethod($request, 'setParameters');
        $parameters = '{"key" : "value"}';

        $this->assertSame(
            ['key' => 'value'],
            $setParameters($parameters)->getParameters()
        );
    }

    /**
     * @test
     * @covers ::setParameters
     * @uses \AyeAye\Api\Request
     * @expectedException        \Exception
     * @expectedExceptionMessage newParameters can not be scalar
     */
    public function testSetParametersException()
    {
        $request = new Request();
        $setParameters = $this->getObjectMethod($request, 'setParameters');
        $parameters = true;

        $setParameters($parameters)->getParameters();
    }

    /**
     * @test
     * @covers ::setParameter
     * @uses \AyeAye\Api\Request
     */
    public function testSetParameter()
    {
        $request = new Request();
        $setParameter = $this->getObjectMethod($request, 'setParameter');

        $this->assertSame(
            ['key' => 'value'],
            $setParameter('key', 'value')->getParameters()
        );
    }

    /**
     * @test
     * @covers ::setParameter
     * @uses \AyeAye\Api\Request
     * @expectedException        \Exception
     * @expectedExceptionMessage Parameter name must be scalar
     */
    public function testSetParameterException()
    {
        $request = new Request();
        $setParameter = $this->getObjectMethod($request, 'setParameter');

        $setParameter([], 'value')->getParameters();
    }
}
