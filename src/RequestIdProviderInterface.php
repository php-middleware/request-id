<?php

namespace PhpMiddleware\RequestId;

interface RequestIdProviderInterface
{
    const DEFAULT_HEADER_REQUEST_ID = 'X-Request-Id';

    public function getRequestId();
}
