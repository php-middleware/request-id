<?php

namespace PhpMiddleware\RequestId;

use Psr\Http\Message\ServerRequestInterface;

interface RequestIdProviderFactoryInterface
{
    public function create(ServerRequestInterface $request): RequestIdProviderInterface;
}