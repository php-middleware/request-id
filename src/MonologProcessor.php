<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\MissingRequestId;

final class MonologProcessor
{
    const KEY = 'request_id';

    private $requestIdProvider;

    public function __construct(RequestIdProviderInterface $requestIdProvider)
    {
        $this->requestIdProvider = $requestIdProvider;
    }

    public function __invoke(array $record): array
    {
        try {
            $requestId = $this->requestIdProvider->getRequestId();
        } catch (MissingRequestId $e) {
            $requestId = null;
        }
        
        $record['extra'][self::KEY] = $requestId;

        return $record;
    }
}
