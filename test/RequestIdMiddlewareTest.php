<?php

namespace PhpMiddlewareTestTest\RequestId;

use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use PhpMiddleware\RequestId\RequestIdProvider;
use PhpMiddleware\RequestId\RequestIdProviderFactoryInterface;
use PhpMiddleware\RequestId\RequestIdProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RequestIdMiddlewareTest extends TestCase
{
    public function testEmmitRequestIdToResponse(): void
    {
        $requestIdProviderFactory = $this->createMock(RequestIdProviderFactoryInterface::class);
        $requestIdProvider = $this->createMock(RequestIdProviderInterface::class);

        $requestIdProviderFactory->method('create')->willReturn($requestIdProvider);
        $requestIdProvider->method('getRequestId')->willReturn('123456789');

        $middleware = new RequestIdMiddleware($requestIdProviderFactory);
        $request = new ServerRequest();

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $result = $middleware->process($request, $handler);

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

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $result = $middleware->process($request, $handler);

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
