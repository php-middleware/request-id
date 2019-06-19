<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\RequestIdExceptionInterface;

interface RequestIdProviderInterface
{
    /**
     * @throws RequestIdExceptionInterface
     */
    public function getRequestId(): string;
}
