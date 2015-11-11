<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    protected function setUp()
    {
        $this->generator = $this->getMock(GeneratorInterface::class);
    }

    public function testGenerateIdAndEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function (ServerRequestInterface $request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testGenerateIdAndNotEmmitToResponse()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator, true, null);
        $request = new ServerRequest();
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertSame($response, $result);
        $this->assertEquals(null, $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    /**
     * @expectedException PhpMiddleware\RequestId\Exception\MissingRequestId
     */
    public function testTryToGetRequestIdBeforeRunMiddleware()
    {
        $middleware = new RequestIdMiddleware($this->generator);
        $middleware->getRequestId();
    }

    /**
     * @dataProvider dataproviderEmptyRequestIdValues
     * @expectedException PhpMiddleware\RequestId\Exception\InvalidRequestId
     */
    public function testTryToGenerateEmptyRequestId($emptyValue)
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn($emptyValue);

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();

        $outFunction = function () {};

        call_user_func($middleware, $request, $response, $outFunction);
    }

    public function dataproviderEmptyRequestIdValues()
    {
        return [
            [''],
            [null],
        ];
    }

    /**
     * @expectedException PhpMiddleware\RequestId\Exception\InvalidRequestId
     */
    public function testTryToGenerateNotStringRequestId()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn(1);

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest();
        $response = new Response();

        call_user_func($middleware, $request, $response, function () {});
    }

    public function testDisallowOverrideButHeaderExists()
    {
        $this->generator->expects($this->once())->method('generateRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($this->generator, false);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('123456789', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testDontGenerateBecouseHeaderExists()
    {
        $this->generator->expects($this->never())->method('generateRequestId');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '987654321']);
        $response = new Response();
        $calledOut = false;

        $outFunction = function ($request, $response) use (&$calledOut) {
            $calledOut = true;

            $this->assertSame('987654321', $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME));

            return $response;
        };

        $result = call_user_func($middleware, $request, $response, $outFunction);

        $this->assertTrue($calledOut, 'Out is not called');
        $this->assertNotSame($response, $result);
        $this->assertEquals('987654321', $result->getHeaderLine(RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID));
        $this->assertSame('987654321', $middleware->getRequestId());
    }

    /**
     * @expectedException PhpMiddleware\RequestId\Exception\MissingRequestId
     */
    public function testDontGenerateBecouseHeaderExistsButEmpty()
    {
        $this->generator->expects($this->never())->method('generateRequestId');

        $middleware = new RequestIdMiddleware($this->generator);
        $request = new ServerRequest([], [], 'https://github.com/php-middleware/request-id', 'GET', 'php://input', [RequestIdMiddleware::DEFAULT_HEADER_REQUEST_ID => '']);
        $response = new Response();

        call_user_func($middleware, $request, $response, function(){});
    }
}
