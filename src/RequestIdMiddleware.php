<?php

namespace PhpMiddleware\RequestId;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PhpMiddleware\DoublePassCompatibilityTrait;
use PhpMiddleware\RequestId\Exception\NotGenerated;
use PhpMiddleware\RequestId\RequestIdProviderFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestIdMiddleware implements RequestIdProviderInterface, MiddlewareInterface
{
    use DoublePassCompatibilityTrait;

    const DEFAULT_RESPONSE_HEADER = 'X-Request-Id';
    const ATTRIBUTE_NAME = 'request-id';

    /**
     * @var RequestIdProviderFactoryInterface
     */
    protected $requestIdProviderFactory;

    /**
     * @var mixed
     */
    protected $requestId;

    /**
     * @var string
     */
    protected $responseHeader;

    /**
     * @param RequestIdProviderFactoryInterface $requestIdProviderFactory
     * @param string $responseHeader
     */
    public function __construct(
        RequestIdProviderFactoryInterface $requestIdProviderFactory,
        $responseHeader = self::DEFAULT_RESPONSE_HEADER
    ) {
        $this->requestIdProviderFactory = $requestIdProviderFactory;
        $this->responseHeader = $responseHeader;
    }

    /**
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $requestIdProvider = $this->requestIdProviderFactory->create($request);
        $this->requestId = $requestIdProvider->getRequestId();
        $requestWithAttribute = $request->withAttribute(self::ATTRIBUTE_NAME, $this->requestId);

        $response = $delegate->process($requestWithAttribute);

        if (is_string($this->responseHeader)) {
            return $response->withHeader($this->responseHeader, $this->requestId);
        }
        return $response;
    }

    /**
     * @return mixed
     *
     * @throws NotGenerated
     */
    public function getRequestId()
    {
        if ($this->requestId === null) {
            throw new NotGenerated('Request id is not generated yet');
        }
        return $this->requestId;
    }
}
