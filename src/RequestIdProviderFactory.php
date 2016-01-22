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
    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var bool|OverridePolicyInterface
     */
    protected $allowOverride;

    /**
     * @var string
     */
    protected $requestHeader;

    /**
     * @param GeneratorInterface $generator
     * @param bool|OverridePolicyInterface $allowOverride
     * @param string $requestHeader
     */
    public function __construct(
        GeneratorInterface $generator,
        $allowOverride = true,
        $requestHeader = RequestIdProvider::DEFAULT_REQUEST_HEADER
    ) {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }

    /**
     * @param ServerRequestInterface $request
     * @return RequestIdProvider
     */
    public function create(ServerRequestInterface $request)
    {
       return new RequestIdProvider($request, $this->generator,  $this->allowOverride,  $this->requestHeader);
    }
}