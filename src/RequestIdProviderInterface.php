<?php

namespace PhpMiddleware\RequestId;

interface RequestIdProviderInterface
{
    public function getRequestId();
}
