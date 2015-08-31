<?php

namespace PhpMiddlewareTest\RequestId;

use PhpMiddlewareTest\RequestId\Generator\GeneratorInterface;
use PhpMiddlewareTest\RequestId\RequestIdAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdMiddleware implements RequestIdAwareInterface
{
    const HEADER_REQUEST_ID = 'X-Request-Id';

    protected $generator;
    protected $allowOverride;
    protected $requestId;
    protected $emmitToResponse;
    protected $headerName;

    public function __construct(GeneratorInterface $generator, $allowOverride = true, $emmitToResponse = true, $headerName = self::HEADER_REQUEST_ID)
    {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->emmitToResponse = $emmitToResponse;
        $this->headerName = $headerName;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $header = $this->headerName;

        if ($this->allowOverride === true && $request->hasHeader($header)) {
            $requestId = $request->getHeaderLine($header);
        } else {
            $requestId = $this->generator->generateRequestId();

            if (empty($requestId)) {
                throw new Exception\NotGenerated('Generator return empty value');
            }
        }

        $this->requestId = $requestId;
        $modifiedRequest = $request->withAttribute($header, $requestId)
                ->withHeader($header, $requestId);

        $outResponse = $out($modifiedRequest, $response);

        if ($this->emmitToResponse === true) {
            $outResponse = $outResponse->withHeader($header, $requestId);
        }
        return $outResponse;
    }

    public function getRequestId()
    {
        if ($this->requestId === null) {
            throw new Exception\NotGenerated('Request id is not generated yet');
        }
        return $this->requestId;
    }
}
