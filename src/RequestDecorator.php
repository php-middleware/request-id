<?php

namespace PhpMiddleware\RequestId;

use Psr\Http\Message\RequestInterface;

final class RequestDecorator
{
    protected $requestIdProvider;
    protected $headerName;

    public function __construct(RequestIdProviderInterface $requestIdProvider, string $headerName = RequestIdMiddleware::DEFAULT_RESPONSE_HEADER)
    {
        $this->requestIdProvider = $requestIdProvider;
        $this->headerName = $headerName;
    }

    /**
     * Adds request id to request and return new instance
     */
    public function decorate(RequestInterface $request): RequestInterface
    {
        return $request->withHeader($this->headerName, $this->requestIdProvider->getRequestId());
    }
}
