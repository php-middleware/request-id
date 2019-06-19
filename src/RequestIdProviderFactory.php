<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @codeCoverageIgnore
 */
final class RequestIdProviderFactory implements RequestIdProviderFactoryInterface
{
    protected $generator;
    protected $allowOverride;
    protected $requestHeader;

    /**
     * @param bool|OverridePolicyInterface $allowOverride
     */
    public function __construct(
        GeneratorInterface $generator,
        $allowOverride = true,
        string $requestHeader = RequestIdProvider::DEFAULT_REQUEST_HEADER
    ) {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }

    public function create(ServerRequestInterface $request): RequestIdProviderInterface
    {
       return new RequestIdProvider($request, $this->generator,  $this->allowOverride,  $this->requestHeader);
    }
}
