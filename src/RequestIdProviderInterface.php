<?php

namespace PhpMiddleware\RequestId;

use PhpMiddleware\RequestId\Exception\RequestIdExceptionInterface;

interface RequestIdProviderInterface
{
    /**
     * @return mixed
     *
     * @throws RequestIdExceptionInterface
     */
    public function getRequestId();
}
