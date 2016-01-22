<?php
namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\InvalidRequestId;
use PhpMiddleware\RequestId\Exception\MissingRequestId;
use PhpMiddleware\RequestId\Generator\GeneratorInterface;
use PhpMiddleware\RequestId\OverridePolicy\OverridePolicyInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestIdProvider  implements RequestIdProviderInterface
{
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

    public function __construct(
        ServerRequestInterface $request,
        GeneratorInterface $generator,
        $allowOverride = true,
        $requestHeader = RequestIdProviderInterface::DEFAULT_HEADER_REQUEST_ID
    )
    {
        $this->request = $request;
        $this->generator = $generator;
        $this->allowOverride = $allowOverride;
        $this->requestHeader = $requestHeader;
    }


    public function getRequestId()
    {
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