<?php

namespace PhpMiddlewareTest\RequestId;

use PhpMiddlewareTest\RequestId\Generator\GeneratorInterface;
use PhpMiddlewareTest\RequestId\RequestIdAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdMiddleware implements RequestIdAwareInterface
{
    const HEADER_REQUEST_ID = 'X-Request-Id';

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
    public function __construct(GeneratorInterface $generator, $allowOverride = true, $emmitToResponse = true, $headerName = self::HEADER_REQUEST_ID)
    {
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->emmitToResponse = $emmitToResponse;
        $this->headerName = $headerName;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $out
     *
     * @return ResponseInterface
     *
     * @throws Exception\NotGenerated
     * @throws Exception\MissingRequestId
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out)
    {
        $header = $this->headerName;

        if ($this->isPossibleToGetFromRequest($request)) {
            $requestId = $request->getHeaderLine($header);

            if (empty($requestId)) {
                throw new Exception\MissingRequestId(sprintf('Missing request id in "%s" request header', $header));
            }
        } else {
            $requestId = $this->generator->generateRequestId();

            if (empty($requestId)) {
                throw new Exception\NotGenerated('Generator return empty value');
            }
            $request = $request->withHeader($header, $requestId);
        }
        $this->requestId = $requestId;

        $outResponse = $out($request, $response);

        if ($this->emmitToResponse === true) {
            $outResponse = $outResponse->withHeader($header, $requestId);
        }
        return $outResponse;
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
     * @return bool
     */
    protected function isPossibleToGetFromRequest(ServerRequestInterface $request)
    {
        return $this->allowOverride === true && $request->hasHeader($this->headerName);
    }
}
