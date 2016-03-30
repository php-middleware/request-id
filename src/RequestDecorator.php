<?php

namespace PhpMiddleware\RequestId;

use Psr\Http\Message\RequestInterface;

final class RequestDecorator
{
    protected $requestIdProvider;
    protected $headerName;

    public function __construct(RequestIdProviderInterface $requestIdProvider, $headerName = RequestIdMiddleware::DEFAULT_RESPONSE_HEADER)
    {
        $this->requestIdProvider = $requestIdProvider;
        $this->headerName = $headerName;
    }

    /**
     * Adds request id to request and return new instance
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function decorate(RequestInterface $request)
    {
        return $request->withHeader($this->headerName, $this->requestIdProvider->getRequestId());
    }
}
