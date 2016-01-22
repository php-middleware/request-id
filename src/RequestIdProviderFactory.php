<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdProviderFactory implements RequestIdProviderFactoryInterface
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
     *
     * @var string
     */
    protected $requestHeader;

    public function __construct(
        GeneratorInterface $generator,
        $allowOverride = true,
        $requestHeader = RequestIdProviderInterface::DEFAULT_HEADER_REQUEST_ID
    )
    {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }

    public function create(ServerRequestInterface $request)
    {
       return new RequestIdProvider($request, $this->generator,  $this->allowOverride,  $this->requestHeader);
    }
}