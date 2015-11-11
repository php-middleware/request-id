<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
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
     * @var bool
     */
    protected $allowOverride;

    /**
     * @var mixed
     */
    protected $requestId;

    /**
     * @var bool
     */
    protected $emmitToResponse;

    /**
     *
     * @var string
     */
    protected $headerName;

    /**
     * @param GeneratorInterface $generator
     * @param bool $allowOverride
     * @param bool $emmitToResponse
     * @param string $headerName
     */
    public function __construct(GeneratorInterface $generator, $allowOverride = true, $emmitToResponse = true, $headerName = self::DEFAULT_HEADER_REQUEST_ID)
    {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->emmitToResponse = $emmitToResponse;
        $this->headerName = $headerName;
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

        if ($this->emmitToResponse === true) {
            return $nextResponse->withHeader($this->headerName, $this->requestId);
        }
        return $nextResponse;
    }

    /**
     * @return mixed
     *
     * @throws Exception\NotGenerated
     */
    public function getRequestId()
    {
        if ($this->requestId === null) {
            throw new Exception\NotGenerated('Request id is not generated yet');
        }
        return $this->requestId;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return mixed
     *
     * @throws Exception\MissingRequestId
     * @throws Exception\NotGenerated
     */
    protected function getRequestIdFromRequest(ServerRequestInterface $request)
    {
        if ($this->isPossibleToGetFromRequest($request)) {
            $requestId = $request->getHeaderLine($this->headerName);

            if (empty($requestId)) {
                throw new Exception\MissingRequestId(sprintf('Missing request id in "%s" request header', $this->headerName));
            }
        } else {
            $requestId = $this->generator->generateRequestId();

            if (empty($requestId)) {
                throw new Exception\NotGenerated('Generator return empty value');
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
        return $this->allowOverride === true && $request->hasHeader($this->headerName);
    }
}
