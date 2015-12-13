<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Exception\NotGenerated;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use PhpMiddleware\RequestId\RequestIdProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdMiddleware implements RequestIdProviderInterface
{
    const DEFAULT_HEADER_REQUEST_ID = 'X-Request-Id';
    const ATTRIBUTE_NAME = 'request-id';

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var bool|OverridePolicyInterface
     */
    protected $allowOverride;

    /**
     * @var mixed
     */
    protected $requestId;

    /**
     * @var string
     */
    protected $responseHeader;

    /**
     *
     * @var string
     */
    protected $requestHeader;

    /**
     * @param GeneratorInterface $generator
     * @param bool|OverridePolicyInterface $allowOverride
     * @param string $responseHeader
     * @param string $requestHeader
     */
    public function __construct(
        GeneratorInterface $generator,
        $allowOverride = true,
        $responseHeader = self::DEFAULT_HEADER_REQUEST_ID,
        $requestHeader = self::DEFAULT_HEADER_REQUEST_ID
    ) {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->responseHeader = $responseHeader;
        $this->requestHeader = $requestHeader;
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
        $this->requestId = $this->getRequestIdFromRequest($request);
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

    /**
     * @param ServerRequestInterface $request
     *
     * @return mixed
     *
     * @throws MissingRequestId
     * @throws NotGenerated
     */
    protected function getRequestIdFromRequest(ServerRequestInterface $request)
    {
        if ($this->isPossibleToGetFromRequest($request)) {
            $requestId = $request->getHeaderLine($this->requestHeader);

            if (empty($requestId)) {
                throw new MissingRequestId(sprintf('Missing request id in "%s" request header', $this->requestHeader));
            }
        } else {
            $requestId = $this->generator->generateRequestId();

            if (empty($requestId)) {
                throw new InvalidRequestId('Generator return empty value');
            }
            if (!is_string($requestId)) {
                throw new InvalidRequestId('Request id is not a string');
            }
        }
        return $requestId;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isPossibleToGetFromRequest(ServerRequestInterface $request)
    {
        if ($this->allowOverride instanceof OverridePolicyInterface) {
            $allowOverride = $this->allowOverride->isAllowToOverride($request);
        } else {
            $allowOverride = $this->allowOverride;
        }

        return $allowOverride === true && $request->hasHeader($this->requestHeader);
    }
}
