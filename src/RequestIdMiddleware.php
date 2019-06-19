<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\NotGenerated;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestIdMiddleware implements RequestIdProviderInterface, MiddlewareInterface
{
    const DEFAULT_RESPONSE_HEADER = 'X-Request-Id';
    const ATTRIBUTE_NAME = 'request-id';

    protected $requestIdProviderFactory;
    protected $requestId;
    protected $responseHeader;

    public function __construct(
        RequestIdProviderFactoryInterface $requestIdProviderFactory,
        ?string $responseHeader = self::DEFAULT_RESPONSE_HEADER
    ) {
        $this->requestIdProviderFactory = $requestIdProviderFactory;
        $this->responseHeader = $responseHeader;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestWithAttribute = $this->attachRequestIdToAttribute($request);

        $response = $handler->handle($requestWithAttribute);

        return $this->attachRequestIdToResponse($response);
    }

    private function attachRequestIdToAttribute(ServerRequestInterface $request): ServerRequestInterface
    {
        $requestIdProvider = $this->requestIdProviderFactory->create($request);
        $this->requestId = $requestIdProvider->getRequestId();

        return $request->withAttribute(self::ATTRIBUTE_NAME, $this->requestId);
    }

    private function attachRequestIdToResponse(ResponseInterface $response): ResponseInterface
    {
        if (is_string($this->responseHeader) && !empty($this->responseHeader)) {
            return $response->withHeader($this->responseHeader, $this->requestId);
        }
        return $response;
    }

    /**
     * @throws NotGenerated
     */
    public function getRequestId(): string
    {
        if ($this->requestId === null) {
            throw new NotGenerated('Request id is not generated yet');
        }
        return $this->requestId;
    }
}
