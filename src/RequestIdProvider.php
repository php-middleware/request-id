<?php
namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestIdProvider implements RequestIdProviderInterface
{
    const DEFAULT_REQUEST_HEADER = 'X-Request-Id';

    /**
     * @var ServerRequestInterface
     */
    protected $request;

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
     *
     * @var string
     */
    protected $requestHeader;

    /**
     * @param ServerRequestInterface $request
     * @param GeneratorInterface $generator
     * @param bool|OverridePolicyInterface $allowOverride
     * @param string $requestHeader
     */
    public function __construct(
        ServerRequestInterface $request,
        GeneratorInterface $generator,
        $allowOverride = true,
        $requestHeader = self::DEFAULT_REQUEST_HEADER
    )
    {
        $this->request = $request;
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }

    /**
     * @return mixed
     *
     * @throws RequestIdExceptionInterface
     */
    public function getRequestId()
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