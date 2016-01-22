<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\NotGenerated;
use PhpMiddleware\RequestId\RequestIdProviderFactoryInterface as RequestIdProviderFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestIdMiddleware implements RequestIdProviderInterface
{
    const DEFAULT_RESPONSE_HEADER = 'X-Request-Id';
    const ATTRIBUTE_NAME = 'request-id';

    /**
     * @var RequestIdProviderFactory
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
     * @param RequestIdProviderFactory $requestIdProviderFactory
     * @param string $responseHeader
     */
    public function __construct(
        RequestIdProviderFactory $requestIdProviderFactory,
        $responseHeader = self::DEFAULT_RESPONSE_HEADER
    ) {
        $this->requestIdProviderFactory = $requestIdProviderFactory;
        $this->responseHeader = $responseHeader;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $requestIdProvider = $this->requestIdProviderFactory->create($request);

        $this->requestId = $requestIdProvider->getRequestId();

        $requestWithAttribute = $request->withAttribute(self::ATTRIBUTE_NAME, $this->requestId);

        $nextResponse = $next($requestWithAttribute, $response);

        if (is_string($this->responseHeader)) {
            return $nextResponse->withHeader($this->responseHeader, $this->requestId);
        }
        return $nextResponse;
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
