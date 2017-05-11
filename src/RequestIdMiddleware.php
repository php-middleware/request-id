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
        $requestWithAttribute = $this->attachRequestIdToAttribute($request);

        $response = $delegate->process($requestWithAttribute);

        if ($this->canAttachToResponse()) {
            return $this->attachRequestIdToResponse($response);
        }
        return $response;
    }

    /**
     * @return ResponseInterface
     */
    private function attachRequestIdToAttribute(ServerRequestInterface $request)
    {
        $requestIdProvider = $this->requestIdProviderFactory->create($request);
        $this->requestId = $requestIdProvider->getRequestId();

        return $request->withAttribute(self::ATTRIBUTE_NAME, $this->requestId);
    }

    /**
     * @return ResponseInterface
     */
    private function attachRequestIdToResponse(ResponseInterface $response)
    {
        return $response->withHeader($this->responseHeader, $this->requestId);
    }

    /**
     * @return bool
     */
    private function canAttachToResponse()
    {
        return is_string($this->responseHeader) && !empty($this->responseHeader);
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
