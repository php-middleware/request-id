<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testEmmitRequestIdToResponse()
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

    public function testNotEmmitRequestIdToResponse()
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

    public function testTryToGetRequestIdBeforeRunMiddleware()
    {
        $this->setExpectedException(MissingRequestId::class);

        $middleware = new RequestIdMiddleware($this->generator);
        $middleware->getRequestId();
    }
}
