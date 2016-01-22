<?php

namespace PhpMiddleware\RequestId;

use Psr\Http\Message\ServerRequestInterface;

interface RequestIdProviderFactoryInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return RequestIdProvider
     */
    public function create(ServerRequestInterface $request);
}