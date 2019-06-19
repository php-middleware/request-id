<?php

namespace PhpMiddleware\RequestId\OverridePolicy;

use Psr\Http\Message\ServerRequestInterface;

interface OverridePolicyInterface
{
    public function isAllowToOverride(ServerRequestInterface $request): bool;
}
