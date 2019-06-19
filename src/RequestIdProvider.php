<?php
namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Exception\RequestIdExceptionInterface;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestIdProvider implements RequestIdProviderInterface
{
    const DEFAULT_REQUEST_HEADER = 'X-Request-Id';

    protected $request;
    protected $generator;
    protected $allowOverride;
    protected $requestId;
    protected $requestHeader;

    /**
     * @param bool|OverridePolicyInterface $allowOverride
     */
    public function __construct(
        ServerRequestInterface $request,
        GeneratorInterface $generator,
        $allowOverride = true,
        string $requestHeader = self::DEFAULT_REQUEST_HEADER
    )
    {
        $this->request = $request;
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }

    /**
     * @throws RequestIdExceptionInterface
     */
    public function getRequestId(): string
    {
        if ($this->requestId !== null) {
            return $this->requestId;
        }

        if ($this->isPossibleToGetFromRequest($this->request)) {
            $requestId = $this->request->getHeaderLine($this->requestHeader);

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
        $this->requestId = $requestId;

        return $requestId;
    }

    protected function isPossibleToGetFromRequest(ServerRequestInterface $request): bool
    {
        if ($this->allowOverride instanceof OverridePolicyInterface) {
            $allowOverride = $this->allowOverride->isAllowToOverride($request);
        } else {
            $allowOverride = $this->allowOverride;
        }

        return $allowOverride === true && $request->hasHeader($this->requestHeader);
    }
}