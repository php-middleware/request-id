<?php

namespace PhpMiddleware\RequestId;

use Psr\Http\Message\ServerRequestInterface;

interface RequestIdProviderFactoryInterface
{
    /**
     * @return RequestIdProviderInterface
     */
    public function create(ServerRequestInterface $request);
}