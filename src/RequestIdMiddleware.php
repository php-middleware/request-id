<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\NotGenerated;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdMiddleware implements RequestIdProviderInterface
{
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
     * @param GeneratorInterface $generator
     * @param bool|OverridePolicyInterface $allowOverride
     * @param string $responseHeader
     * @param string $requestHeader
     */
    public function __construct(RequestIdProviderFactoryInterface $requestIdProviderFactory, $responseHeader = RequestIdProviderInterface::DEFAULT_HEADER_REQUEST_ID) {
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
        /** @var RequestIdProviderInterface $requestIdProvider */
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
