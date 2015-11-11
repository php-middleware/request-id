<?php

namespace PhpMiddleware\RequestId;

final class MonologProcessor
{
    const KEY = 'request_id';

    private $requestIdProvider;

    public function __construct(RequestIdProviderInterface $requestIdProvider)
    {
        $this->requestIdProvider = $requestIdProvider;
    }

    public function __invoke(array $record)
    {
        $record['extra'][self::KEY] = $this->requestIdProvider->getRequestId();

        return $record;
    }
}
