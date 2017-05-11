<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PhpMiddleware\RequestId\RequestIdProvider;
use PhpMiddleware\RequestId\RequestIdProviderFactoryInterface;
use PhpMiddleware\RequestId\RequestIdProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends TestCase
{
    public function testEmmitRequestIdToResponse()
    {
        $requestIdProviderFactory = $this->createMock(RequestIdProviderFactoryInterface::class);
        $requestIdProvider = $this->createMock(RequestIdProviderInterface::class);

        $requestIdProviderFactory->method('create')->willReturn($requestIdProvider);
        $requestIdProvider->method('getRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($requestIdProviderFactory);
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
        $this->assertEquals('123456789', $result->getHeaderLine(RequestIdProvider::DEFAULT_REQUEST_HEADER));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testNotEmmitRequestIdToResponse()
    {
        $requestIdProviderFactory = $this->createMock(RequestIdProviderFactoryInterface::class);
        $requestIdProvider = $this->createMock(RequestIdProviderInterface::class);

        $requestIdProviderFactory->method('create')->willReturn($requestIdProvider);
        $requestIdProvider->method('getRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($requestIdProviderFactory, null);
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
        $this->assertEquals(null, $result->getHeaderLine(RequestIdProvider::DEFAULT_REQUEST_HEADER));
        $this->assertSame('123456789', $middleware->getRequestId());
    }

    public function testTryToGetRequestIdBeforeRunMiddleware()
    {
        $requestIdProviderFactory = $this->createMock(RequestIdProviderFactoryInterface::class);

        $middleware = new RequestIdMiddleware($requestIdProviderFactory);

        $this->expectException(MissingRequestId::class);

        $middleware->getRequestId();
    }
}
